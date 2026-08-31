import pytest

from app.application.dto.disease_detection_dto import DiseaseDetectionInput
from app.application.use_cases.detect_disease import DetectDiseaseUseCase
from app.core.exceptions import ImageValidationError
from app.domain.services.confidence_policy import ConfidencePolicy
from app.domain.services.image_quality_policy import ImageQualityPolicy
from app.domain.services.leaf_validation_policy import LeafValidationPolicy


class FakePreprocessor:
    def __init__(self, leaf_features=None):
        self.leaf_features = leaf_features or {
            "leaf_ratio": 0.65,
            "skin_ratio": 0.02,
            "unnatural_ratio": 0.01,
            "mean_saturation": 85.0,
        }

    def decode(self, content):
        return "decoded-image"

    def measure_quality(self, image_rgb):
        return 150.0, 120.0

    def analyze_leaf_features(self, image_rgb):
        return self.leaf_features


class LowConfidenceModel:
    model_version = "test"
    is_loaded = True

    def __init__(self, confidence=0.40):
        self.confidence = confidence

    def predict(self, image_rgb):
        return "blast", "Blast", self.confidence


def _use_case(max_size=1024, preprocessor=None, model=None, min_confidence=0.35):
    return DetectDiseaseUseCase(
        model_repository=model or LowConfidenceModel(),
        image_preprocessor=preprocessor or FakePreprocessor(),
        confidence_policy=ConfidencePolicy(0.85, 0.70),
        image_quality_policy=ImageQualityPolicy(100, 40, 220),
        max_image_size_bytes=max_size,
        leaf_validation_policy=LeafValidationPolicy(min_leaf_ratio=0.12, min_disease_confidence=min_confidence),
    )


def test_detect_disease_marks_low_confidence_for_expert_review():
    prediction = _use_case().execute(
        DiseaseDetectionInput(content=b"\xff\xd8\xfffake", content_type="image/jpeg", filename="leaf.jpg")
    )

    assert prediction.confidence_level == "low"
    assert prediction.needs_expert_review is True


def test_detect_disease_rejects_empty_file():
    with pytest.raises(ImageValidationError) as error_info:
        _use_case().execute(DiseaseDetectionInput(content=b"", content_type="image/jpeg", filename="leaf.jpg"))

    assert error_info.value.code == "EMPTY_IMAGE"


def test_detect_disease_rejects_unsupported_format():
    with pytest.raises(ImageValidationError) as error_info:
        _use_case().execute(DiseaseDetectionInput(content=b"GIF89a", content_type="image/gif", filename="leaf.gif"))

    assert error_info.value.code == "UNSUPPORTED_IMAGE_TYPE"


def test_detect_disease_rejects_non_leaf_image():
    non_leaf_preprocessor = FakePreprocessor(
        leaf_features={
            "leaf_ratio": 0.02,
            "skin_ratio": 0.01,
            "unnatural_ratio": 0.01,
            "mean_saturation": 30.0,
        }
    )
    with pytest.raises(ImageValidationError) as error_info:
        _use_case(preprocessor=non_leaf_preprocessor).execute(
            DiseaseDetectionInput(content=b"\xff\xd8\xfffake", content_type="image/jpeg", filename="not_leaf.jpg")
        )

    assert error_info.value.code == "IMAGE_NOT_LEAF"


def test_detect_disease_rejects_human_skin_image():
    skin_preprocessor = FakePreprocessor(
        leaf_features={
            "leaf_ratio": 0.05,
            "skin_ratio": 0.45,
            "unnatural_ratio": 0.02,
            "mean_saturation": 50.0,
        }
    )
    with pytest.raises(ImageValidationError) as error_info:
        _use_case(preprocessor=skin_preprocessor).execute(
            DiseaseDetectionInput(content=b"\xff\xd8\xfffake", content_type="image/jpeg", filename="selfie.jpg")
        )

    assert error_info.value.code == "IMAGE_NOT_LEAF_HUMAN"


def test_detect_disease_rejects_unrecognized_pattern():
    very_low_confidence_model = LowConfidenceModel(confidence=0.15)
    with pytest.raises(ImageValidationError) as error_info:
        _use_case(model=very_low_confidence_model, min_confidence=0.35).execute(
            DiseaseDetectionInput(content=b"\xff\xd8\xfffake", content_type="image/jpeg", filename="leaf.jpg")
        )

    assert error_info.value.code == "IMAGE_NOT_LEAF_UNRECOGNIZED"
