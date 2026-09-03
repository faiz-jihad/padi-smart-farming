from __future__ import annotations

import hashlib

from app.core.constants import SUPPORTED_DISEASE_CODES
from app.core.exceptions import ImageValidationError
from app.domain.services.leaf_memory_bank import LeafMemoryBank, LearnedLeafSample
from app.domain.services.leaf_validation_policy import LeafValidationPolicy
from app.infrastructure.machine_learning.disease_classifier import DiseaseClassifier
from app.infrastructure.machine_learning.image_preprocessor import ImagePreprocessor


class LearnDiseaseScanUseCase:
    """Use case untuk mendaftarkan foto daun yang terverifikasi ke dalam memory bank pembelajaran."""

    ALLOWED_SOURCES = {"farmer_confirmed", "farmer_corrected", "expert_verified"}

    def __init__(
        self,
        image_preprocessor: ImagePreprocessor,
        disease_classifier: DiseaseClassifier,
        leaf_memory_bank: LeafMemoryBank,
        leaf_validation_policy: LeafValidationPolicy | None = None,
    ) -> None:
        self.image_preprocessor = image_preprocessor
        self.disease_classifier = disease_classifier
        self.leaf_memory_bank = leaf_memory_bank
        self.leaf_validation_policy = leaf_validation_policy or LeafValidationPolicy()

    def execute(
        self,
        content: bytes,
        disease_code: str,
        disease_name: str,
        confidence: float = 1.0,
        source: str = "farmer_confirmed",
        sample_id: str | None = None,
    ) -> LearnedLeafSample:
        """Memvalidasi dan menyimpan sampel daun ke dalam memory bank."""
        if not content:
            raise ImageValidationError("File gambar kosong.", code="EMPTY_IMAGE")

        normalized_disease_code = disease_code.strip().lower()
        if normalized_disease_code not in SUPPORTED_DISEASE_CODES or normalized_disease_code == "unknown":
            raise ImageValidationError(
                "Kode penyakit tidak didukung untuk pembelajaran.",
                code="UNSUPPORTED_DISEASE_CODE",
            )

        normalized_source = source.strip().lower()
        if normalized_source not in self.ALLOWED_SOURCES:
            raise ImageValidationError(
                "Sampel pembelajaran harus berasal dari konfirmasi/koreksi petani atau verifikasi pakar.",
                code="UNSUPPORTED_LEARNING_SOURCE",
            )

        image_rgb = self.image_preprocessor.decode(content)

        # Pastikan gambar adalah daun padi yang valid
        leaf_features = self.image_preprocessor.analyze_leaf_features(image_rgb)
        leaf_decision = self.leaf_validation_policy.evaluate_visual_features(
            leaf_ratio=leaf_features["leaf_ratio"],
            skin_ratio=leaf_features["skin_ratio"],
            mean_saturation=leaf_features["mean_saturation"],
            unnatural_ratio=leaf_features["unnatural_ratio"],
        )
        if not leaf_decision.is_acceptable:
            raise ImageValidationError(
                leaf_decision.error_message or "Objek yang didaftarkan bukan daun padi.",
                code="IMAGE_NOT_LEAF",
            )

        # Ekstraksi fitur visual daun
        feature_vector = self.disease_classifier.extract_feature_vector(image_rgb)
        resolved_sample_id = sample_id or hashlib.sha256(content).hexdigest()[:16]

        sample = self.leaf_memory_bank.add_sample(
            sample_id=resolved_sample_id,
            disease_code=normalized_disease_code,
            disease_name=disease_name,
            feature_vector=feature_vector,
            confidence=confidence,
            source=normalized_source,
        )

        return sample
