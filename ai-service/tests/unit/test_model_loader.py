from pathlib import Path

import numpy as np

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


class FakeModel:
    def predict(self, model_input, verbose=0):
        return np.array([[1.0, 4.0, 2.0]], dtype=np.float32)


class FakeLoader:
    def load(self, model_path):
        return FakeModel()


class FakePreprocessor:
    def preprocess_for_model(self, image_rgb):
        return np.zeros((1, 224, 224, 3), dtype=np.float32)


def test_classifier_normalizes_logits_and_returns_top_predictions():
    classifier = DiseaseClassifier(
        model_path=Path("unused.h5"),
        model_version="test",
        image_preprocessor=FakePreprocessor(),
        label_mapper=LabelMapper({"0": "healthy", "1": "blast", "2": "tungro"}),
        model_loader=FakeLoader(),
    )
    classifier.load()

    disease_code, _, confidence, top_predictions, prediction_margin = classifier.predict_top("image", top_k=2)

    assert disease_code == "blast"
    assert top_predictions[0]["disease_code"] == "blast"
    assert top_predictions[1]["disease_code"] == "tungro"
    assert 0.80 < confidence < 0.90
    assert prediction_margin > 0.70
