from __future__ import annotations

import logging
import time

from app.application.dto.disease_detection_dto import DiseaseDetectionInput
from app.core.constants import SUPPORTED_IMAGE_SIGNATURES
from app.core.exceptions import ImageValidationError, ModelUnavailableError
from app.domain.entities.disease_prediction import DiseasePrediction, ImageQuality, PredictionCandidate
from app.domain.repositories.disease_model_repository import DiseaseModelRepository
from app.domain.services.confidence_policy import ConfidencePolicy
from app.domain.services.image_quality_policy import ImageQualityPolicy
from app.domain.services.leaf_memory_bank import LeafMemoryBank
from app.domain.services.leaf_validation_policy import LeafValidationPolicy
from app.infrastructure.machine_learning.image_preprocessor import ImagePreprocessor

logger = logging.getLogger(__name__)

# Threshold margin prediksi — jika margin antar kandidat terlalu kecil,
# model tidak cukup yakin untuk memberikan diagnosis definitif.
# Margin = confidence(top-1) - confidence(top-2)
_MIN_PREDICTION_MARGIN = 0.12
# Threshold confidence minimum untuk "DETECTED" (bukan UNCERTAIN)
# Jika confidence di bawah ini SETELAH lolos minimum confidence policy,
# tetapi margin masih sempit, kondisi dianggap ambigu.
_UNCERTAIN_CONFIDENCE_CAP = 0.72


class DetectDiseaseUseCase:
    def __init__(
        self,
        model_repository: DiseaseModelRepository,
        image_preprocessor: ImagePreprocessor,
        confidence_policy: ConfidencePolicy,
        image_quality_policy: ImageQualityPolicy,
        max_image_size_bytes: int,
        leaf_validation_policy: LeafValidationPolicy | None = None,
        leaf_memory_bank: LeafMemoryBank | None = None,
        model_accuracy: float | None = None,
    ) -> None:
        self.model_repository = model_repository
        self.image_preprocessor = image_preprocessor
        self.confidence_policy = confidence_policy
        self.image_quality_policy = image_quality_policy
        self.max_image_size_bytes = max_image_size_bytes
        self.leaf_validation_policy = leaf_validation_policy or LeafValidationPolicy()
        self.leaf_memory_bank = leaf_memory_bank
        self.model_accuracy = model_accuracy

    def execute(self, detection_input: DiseaseDetectionInput) -> DiseasePrediction:
        """Menjalankan alur validasi gambar sampai inferensi penyakit dengan pembelajaran adaptif.

        Pipeline:
        1. Validasi upload (ukuran, format, signature)
        2. Decode + EXIF orientation fix
        3. Quality gate (blur, brightness)
        4. Leaf validation (warna, kulit, monokrom)
        5. Model inference (top-3 predictions)
        6. Memory bank refinement (k-NN cosine similarity)
        7. Margin-based uncertainty check
        8. Confidence level assignment
        """
        started_at = time.perf_counter()
        self._validate_upload(detection_input)

        image_rgb = self.image_preprocessor.decode(detection_input.content)
        blur_score, brightness_score = self.image_preprocessor.measure_quality(image_rgb)
        img_h, img_w = self.image_preprocessor.get_resolution(image_rgb)
        quality_decision = self.image_quality_policy.evaluate(
            blur_score, brightness_score, image_height=img_h, image_width=img_w
        )
        if not quality_decision.is_acceptable:
            raise ImageValidationError(
                quality_decision.error_message or "Kualitas gambar tidak memenuhi syarat.",
                code=quality_decision.error_code or "IMAGE_QUALITY_REJECTED",
            )

        # --- Validasi apakah objek adalah daun padi ---
        leaf_features = self.image_preprocessor.analyze_leaf_features(image_rgb)
        leaf_decision = self.leaf_validation_policy.evaluate_visual_features(
            leaf_ratio=leaf_features["leaf_ratio"],
            skin_ratio=leaf_features["skin_ratio"],
            mean_saturation=leaf_features["mean_saturation"],
            unnatural_ratio=leaf_features["unnatural_ratio"],
            green_ratio=leaf_features.get("green_ratio", leaf_features["leaf_ratio"]),
        )
        if not leaf_decision.is_acceptable:
            raise ImageValidationError(
                leaf_decision.error_message or "Objek pada gambar bukan daun padi.",
                code=leaf_decision.error_code or "IMAGE_NOT_LEAF",
            )

        combined_warnings = list(quality_decision.warnings) + list(leaf_decision.warnings)
        image_quality = ImageQuality(
            is_acceptable=True,
            blur_score=round(blur_score, 2),
            brightness_score=round(brightness_score, 2),
            warnings=combined_warnings,
        )

        # --- Prediksi model + ekstraksi feature vector ---
        top_predictions: list[PredictionCandidate] = []
        prediction_margin = 0.0
        feature_vector = None

        if hasattr(self.model_repository, "predict_top"):
            disease_code, disease_name, confidence, candidates, prediction_margin = (
                self.model_repository.predict_top(image_rgb, top_k=3)
            )
            top_predictions = [
                PredictionCandidate(
                    disease_code=str(c["disease_code"]),
                    disease_name=str(c["disease_name"]),
                    confidence=round(float(c["confidence"]), 4),
                )
                for c in candidates
            ]
            if hasattr(self.model_repository, "extract_feature_vector"):
                feature_vector = self.model_repository.extract_feature_vector(image_rgb)
        elif hasattr(self.model_repository, "predict_with_embedding"):
            disease_code, disease_name, confidence, feature_vector = (
                self.model_repository.predict_with_embedding(image_rgb)
            )
        else:
            disease_code, disease_name, confidence = self.model_repository.predict(image_rgb)
            feature_vector = None

        if disease_code == "unknown" or disease_name == "Tidak Dapat Dipastikan":
            raise ModelUnavailableError(
                "Mapping label model belum valid untuk hasil deteksi.",
                code="MODEL_LABEL_MAPPING_INVALID",
            )

        # --- Pembelajaran Berkelanjutan: Memory Bank k-NN Refinement ---
        if self.leaf_memory_bank is not None and feature_vector is not None:
            try:
                refined_code, refined_name, refined_conf, is_boosted = (
                    self.leaf_memory_bank.compute_memory_refinement(
                        query_vector=feature_vector,
                        base_disease_code=disease_code,
                        base_confidence=confidence,
                    )
                )
                if is_boosted:
                    disease_code = refined_code
                    if refined_name:
                        disease_name = refined_name
                    confidence = refined_conf
                    logger.debug(
                        "event=memory_refinement_applied disease=%s conf=%.3f",
                        disease_code,
                        confidence,
                    )
            except Exception as exc:
                logger.warning("event=memory_refinement_skipped error=%s", exc)

        # --- Sinkronisasi top_predictions dengan hasil akhir ---
        if not top_predictions:
            top_predictions = [
                PredictionCandidate(
                    disease_code=disease_code,
                    disease_name=disease_name,
                    confidence=round(confidence, 4),
                )
            ]
        elif top_predictions[0].disease_code == disease_code:
            top_predictions[0] = PredictionCandidate(
                disease_code=disease_code,
                disease_name=disease_name,
                confidence=round(confidence, 4),
            )
        else:
            top_predictions.insert(
                0,
                PredictionCandidate(
                    disease_code=disease_code,
                    disease_name=disease_name,
                    confidence=round(confidence, 4),
                ),
            )

        # Recalculate margin dari top-predictions yang sudah disinkronisasi
        prediction_margin = (
            max(0.0, top_predictions[0].confidence - top_predictions[1].confidence)
            if len(top_predictions) > 1
            else prediction_margin
        )

        # --- Validasi minimum confidence model terhadap pola daun padi ---
        model_leaf_decision = self.leaf_validation_policy.evaluate_model_confidence(
            confidence=confidence,
            leaf_ratio=leaf_features["leaf_ratio"],
        )
        if not model_leaf_decision.is_acceptable:
            raise ImageValidationError(
                model_leaf_decision.error_message or "Pola daun padi tidak teridentifikasi.",
                code=model_leaf_decision.error_code or "IMAGE_NOT_LEAF_UNRECOGNIZED",
            )

        # --- MARGIN-BASED UNCERTAINTY CHECK ---
        # Jika margin antara kandidat 1 dan kandidat 2 terlalu sempit DAN
        # confidence secara absolut juga tidak tinggi, hasilnya UNCERTAIN.
        # Ini mencegah diagnosis definitif ketika model ragu-ragu.
        detection_status = "DETECTED"
        status_message: str | None = None

        is_narrow_margin = prediction_margin < _MIN_PREDICTION_MARGIN
        is_low_absolute_confidence = confidence < _UNCERTAIN_CONFIDENCE_CAP
        is_uncertain = is_narrow_margin and is_low_absolute_confidence

        if is_uncertain:
            detection_status = "UNCERTAIN"
            top_2_name = (
                top_predictions[1].disease_name if len(top_predictions) > 1 else "kondisi lain"
            )
            top_2_conf = (
                f"{top_predictions[1].confidence:.0%}" if len(top_predictions) > 1 else "?"
            )
            status_message = (
                f"Deteksi tidak dapat dipastikan — model ragu antara "
                f"'{disease_name}' ({confidence:.0%}) dan '{top_2_name}' "
                f"({top_2_conf}). "
                f"Ambil foto lebih dekat dengan pencahayaan merata, atau konsultasikan "
                f"dengan penyuluh pertanian."
            )
            logger.info(
                "event=detection_uncertain disease=%s conf=%.3f margin=%.3f",
                disease_code,
                confidence,
                prediction_margin,
            )

        confidence_decision = self.confidence_policy.evaluate(confidence)
        processing_time_ms = int((time.perf_counter() - started_at) * 1000)

        return DiseasePrediction(
            disease_code=disease_code,
            disease_name=disease_name,
            confidence=round(confidence, 4),
            confidence_level=confidence_decision.level,
            image_quality=image_quality,
            needs_expert_review=confidence_decision.needs_expert_review or is_uncertain,
            model_version=self.model_repository.model_version,
            processing_time_ms=processing_time_ms,
            top_predictions=top_predictions,
            prediction_margin=round(prediction_margin, 4),
            model_accuracy=self.model_accuracy,
            detection_status=detection_status,
            status_message=status_message,
        )

    def _validate_upload(self, detection_input: DiseaseDetectionInput) -> None:
        content = detection_input.content
        if not content:
            raise ImageValidationError("File gambar kosong.", code="EMPTY_IMAGE")
        if len(content) > self.max_image_size_bytes:
            raise ImageValidationError(
                "Ukuran gambar melebihi batas maksimum.", code="IMAGE_TOO_LARGE"
            )
        if detection_input.content_type not in SUPPORTED_IMAGE_SIGNATURES:
            raise ImageValidationError(
                "Format gambar tidak didukung. Gunakan JPEG, JPG, atau PNG.",
                code="UNSUPPORTED_IMAGE_TYPE",
            )

        signatures = SUPPORTED_IMAGE_SIGNATURES[detection_input.content_type]
        if not any(content.startswith(sig) for sig in signatures):
            raise ImageValidationError(
                "Signature file tidak sesuai dengan format gambar.",
                code="INVALID_IMAGE_SIGNATURE",
            )
