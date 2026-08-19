from pathlib import Path

from app.infrastructure.machine_learning.disease_classifier import DiseaseClassifier
from app.infrastructure.machine_learning.image_preprocessor import ImagePreprocessor
from app.infrastructure.machine_learning.label_mapper import LabelMapper


def test_model_load_failure_is_reported_without_crashing():
    classifier = DiseaseClassifier(
        model_path=Path("missing-model.h5"),
        model_version="test",
        image_preprocessor=ImagePreprocessor(),
        label_mapper=LabelMapper({}),
    )

    classifier.load()

    assert classifier.is_loaded is False
    assert classifier.load_error == "MODEL_NOT_FOUND"
