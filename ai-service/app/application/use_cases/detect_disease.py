from __future__ import annotations

import time

from app.application.dto.disease_detection_dto import DiseaseDetectionInput
from app.core.constants import SUPPORTED_IMAGE_SIGNATURES
from app.core.exceptions import ImageValidationError
from app.domain.entities.disease_prediction import DiseasePrediction, ImageQuality
from app.domain.repositories.disease_model_repository import DiseaseModelRepository
from app.domain.services.confidence_policy import ConfidencePolicy
from app.domain.services.image_quality_policy import ImageQualityPolicy
from app.infrastructure.machine_learning.image_preprocessor import ImagePreprocessor


class DetectDiseaseUseCase:
    def __init__(
        self,
        model_repository: DiseaseModelRepository,
        image_preprocessor: ImagePreprocessor,
        confidence_policy: ConfidencePolicy,
        image_quality_policy: ImageQualityPolicy,
        max_image_size_bytes: int,
    ) -> None:
        self.model_repository = model_repository
        self.image_preprocessor = image_preprocessor
        self.confidence_policy = confidence_policy
        self.image_quality_policy = image_quality_policy
        self.max_image_size_bytes = max_image_size_bytes

    def execute(self, detection_input: DiseaseDetectionInput) -> DiseasePrediction:
        """Menjalankan alur validasi gambar sampai inferensi penyakit."""
        started_at = time.perf_counter()
        self._validate_upload(detection_input)

        image_rgb = self.image_preprocessor.decode(detection_input.content)
        blur_score, brightness_score = self.image_preprocessor.measure_quality(image_rgb)
        quality_decision = self.image_quality_policy.evaluate(blur_score, brightness_score)
        image_quality = ImageQuality(
            is_acceptable=quality_decision.is_acceptable,
            blur_score=round(blur_score, 2),
            brightness_score=round(brightness_score, 2),
            warnings=quality_decision.warnings,
        )
        if not quality_decision.is_acceptable:
            raise ImageValidationError(
                quality_decision.error_message or "Kualitas gambar tidak memenuhi syarat.",
                code=quality_decision.error_code or "IMAGE_QUALITY_REJECTED",
            )

        disease_code, disease_name, confidence = self.model_repository.predict(image_rgb)
        confidence_decision = self.confidence_policy.evaluate(confidence)
        processing_time_ms = int((time.perf_counter() - started_at) * 1000)

        return DiseasePrediction(
            disease_code=disease_code,
            disease_name=disease_name,
            confidence=round(confidence, 4),
            confidence_level=confidence_decision.level,
            image_quality=image_quality,
            needs_expert_review=confidence_decision.needs_expert_review,
            model_version=self.model_repository.model_version,
            processing_time_ms=processing_time_ms,
        )

    def _validate_upload(self, detection_input: DiseaseDetectionInput) -> None:
        content = detection_input.content
        if not content:
            raise ImageValidationError("File gambar kosong.", code="EMPTY_IMAGE")
        if len(content) > self.max_image_size_bytes:
            raise ImageValidationError("Ukuran gambar melebihi batas maksimum.", code="IMAGE_TOO_LARGE")
        if detection_input.content_type not in SUPPORTED_IMAGE_SIGNATURES:
            raise ImageValidationError(
                "Format gambar tidak didukung. Gunakan JPEG, JPG, atau PNG.", code="UNSUPPORTED_IMAGE_TYPE"
            )

        signatures = SUPPORTED_IMAGE_SIGNATURES[detection_input.content_type]
        if not any(content.startswith(signature) for signature in signatures):
            raise ImageValidationError(
                "Signature file tidak sesuai dengan format gambar.", code="INVALID_IMAGE_SIGNATURE"
            )
