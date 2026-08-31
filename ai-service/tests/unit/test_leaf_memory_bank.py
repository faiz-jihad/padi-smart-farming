import numpy as np
import pytest

from app.application.use_cases.learn_disease_scan import LearnDiseaseScanUseCase
from app.core.exceptions import ImageValidationError
from app.domain.services.leaf_memory_bank import LeafMemoryBank


def test_leaf_memory_bank_add_and_retrieve():
    bank = LeafMemoryBank()
    v1 = np.array([1.0, 0.0, 0.0, 0.0], dtype=np.float32)
    bank.add_sample("leaf-1", "blast", "Blast", v1, 0.95, source="farmer_confirmed")

    assert bank.total_samples == 1
    stats = bank.get_stats()
    assert stats["total_learned_leaves"] == 1
    assert stats["disease_distribution"]["blast"] == 1


def test_leaf_memory_bank_nearest_neighbors():
    bank = LeafMemoryBank()
    bank.add_sample("leaf-blast", "blast", "Blast", [1.0, 0.0, 0.0], 0.95)
    bank.add_sample("leaf-tungro", "tungro", "Tungro", [0.0, 1.0, 0.0], 0.90)

    # Daun query mirip blast
    query = [0.95, 0.05, 0.0]
    neighbors = bank.find_nearest_neighbors(query, top_k=2)

    assert len(neighbors) >= 1
    assert neighbors[0][0].disease_code == "blast"
    assert neighbors[0][1] > 0.9


def test_leaf_memory_refinement_boosts_confidence():
    bank = LeafMemoryBank()
    bank.add_sample("leaf-1", "blast", "Blast", [1.0, 0.0], 0.95, source="expert_verified")

    query = [0.99, 0.01]
    disease_code, disease_name, conf, is_boosted = bank.compute_memory_refinement(
        query_vector=query,
        base_disease_code="blast",
        base_confidence=0.80,
    )

    assert is_boosted is True
    assert disease_code == "blast"
    assert conf > 0.80


def test_leaf_memory_refinement_corrects_prediction_when_past_expert_is_strong():
    bank = LeafMemoryBank()
    # Ahli pernah memverifikasi pola visual ini sebenarnya tungro
    bank.add_sample("verified-leaf", "tungro", "Tungro", [0.0, 1.0], 0.98, source="expert_verified")

    query = [0.01, 0.99]
    disease_code, disease_name, conf, is_boosted = bank.compute_memory_refinement(
        query_vector=query,
        base_disease_code="blast",
        base_confidence=0.70,
    )

    assert is_boosted is True
    assert disease_code == "tungro"
    assert disease_name == "Tungro"


def test_leaf_memory_refinement_uses_farmer_corrections():
    bank = LeafMemoryBank()
    bank.add_sample("corrected-leaf", "tungro", "Tungro", [0.0, 1.0], 0.98, source="farmer_corrected")

    disease_code, disease_name, _, is_boosted = bank.compute_memory_refinement(
        query_vector=[0.01, 0.99],
        base_disease_code="blast",
        base_confidence=0.70,
    )

    assert is_boosted is True
    assert disease_code == "tungro"
    assert disease_name == "Tungro"


def test_leaf_memory_load_ignores_untrusted_auto_scan_samples(tmp_path):
    memory_path = tmp_path / "leaf_memory_bank.json"
    memory_path.write_text(
        """
{
  "auto-blast": {
    "sample_id": "auto-blast",
    "disease_code": "blast",
    "disease_name": "Blast",
    "feature_vector": [1.0, 0.0],
    "confidence": 0.99,
    "source": "auto_scan_pipeline",
    "learned_at": "2026-08-31T00:00:00+00:00"
  },
  "verified-tungro": {
    "sample_id": "verified-tungro",
    "disease_code": "tungro",
    "disease_name": "Tungro",
    "feature_vector": [0.0, 1.0],
    "confidence": 0.98,
    "source": "farmer_corrected",
    "learned_at": "2026-08-31T00:00:00+00:00"
  }
}
""",
        encoding="utf-8",
    )

    bank = LeafMemoryBank(storage_path=memory_path)

    assert bank.total_samples == 1
    assert bank.get_stats()["disease_distribution"] == {"tungro": 1}


class FakePreprocessor:
    def decode(self, content):
        return "decoded-image"

    def analyze_leaf_features(self, image_rgb):
        return {
            "leaf_ratio": 0.65,
            "skin_ratio": 0.02,
            "unnatural_ratio": 0.01,
            "mean_saturation": 85.0,
        }


class FakeClassifier:
    def extract_feature_vector(self, image_rgb):
        return np.array([1.0, 0.0], dtype=np.float32)


def test_learn_use_case_rejects_untrusted_source():
    use_case = LearnDiseaseScanUseCase(
        image_preprocessor=FakePreprocessor(),
        disease_classifier=FakeClassifier(),
        leaf_memory_bank=LeafMemoryBank(),
    )

    with pytest.raises(ImageValidationError) as error_info:
        use_case.execute(
            content=b"fake-image",
            disease_code="blast",
            disease_name="Blast",
            source="auto_scan_pipeline",
        )

    assert error_info.value.code == "UNSUPPORTED_LEARNING_SOURCE"
