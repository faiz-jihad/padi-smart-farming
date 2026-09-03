from pathlib import Path

import numpy as np

from app.infrastructure.machine_learning.label_mapper import LabelMapper
from app.infrastructure.machine_learning.yolo_disease_detector import YoloDiseaseDetector


class FakeYoloModel:
    def __init__(self, result):
        self.names = {
            0: "水稻白叶枯病Bacterial_Leaf_Blight",
            1: "稻瘟病Leaf_Blast",
            2: "健康水稻HealthyLeaf",
        }
        self._result = result

    def predict(self, source, verbose=False):
        return [self._result]


class FakeClassificationResult:
    names = {
        0: "水稻白叶枯病Bacterial_Leaf_Blight",
        1: "稻瘟病Leaf_Blast",
        2: "健康水稻HealthyLeaf",
    }

    class Probs:
        data = np.array([0.82, 0.10, 0.08], dtype=np.float32)

    probs = Probs()


class FakeBoxes:
    cls = np.array([1, 0, 0], dtype=np.float32)
    conf = np.array([0.40, 0.91, 0.72], dtype=np.float32)


class FakeDetectionResult:
    names = {
        0: "水稻白叶枯病Bacterial_Leaf_Blight",
        1: "稻瘟病Leaf_Blast",
    }
    boxes = FakeBoxes()


def _detector_with_result(result):
    detector = YoloDiseaseDetector(
        model_path=Path("unused.pt"),
        model_version="test-yolo",
        label_mapper=LabelMapper({}),
    )
    detector._model = FakeYoloModel(result)
    return detector


def test_yolo_classifier_uses_model_names_for_classification_probs():
    detector = _detector_with_result(FakeClassificationResult())

    disease_code, disease_name, confidence, top_predictions, prediction_margin = detector.predict_top("image")

    assert disease_code == "bacterial_leaf_blight"
    assert "Hawar Daun Bakteri" in disease_name
    assert confidence == 0.82
    assert top_predictions[0]["disease_code"] == "bacterial_leaf_blight"
    assert prediction_margin > 0.70


def test_yolo_detector_aggregates_detection_boxes_by_best_class_confidence():
    detector = _detector_with_result(FakeDetectionResult())

    disease_code, _, confidence, top_predictions, _ = detector.predict_top("image")

    assert disease_code == "bacterial_leaf_blight"
    assert confidence == 0.91
    assert top_predictions[1]["disease_code"] == "blast"
