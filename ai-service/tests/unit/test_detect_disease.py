import pytest

from app.application.dto.disease_detection_dto import DiseaseDetectionInput
from app.application.use_cases.detect_disease import DetectDiseaseUseCase
from app.core.exceptions import ImageValidationError
from app.domain.services.confidence_policy import ConfidencePolicy
from app.domain.services.image_quality_policy import ImageQualityPolicy


class FakePreprocessor:
    def decode(self, content):
        return "decoded-image"

    def measure_quality(self, image_rgb):
        return 150.0, 120.0


class LowConfidenceModel:
    model_version = "test"
    is_loaded = True

    def predict(self, image_rgb):
        return "blast", "Blast", 0.4


def _use_case(max_size=1024):
    return DetectDiseaseUseCase(
        model_repository=LowConfidenceModel(),
        image_preprocessor=FakePreprocessor(),
        confidence_policy=ConfidencePolicy(0.85, 0.70),
        image_quality_policy=ImageQualityPolicy(100, 40, 220),
        max_image_size_bytes=max_size,
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
