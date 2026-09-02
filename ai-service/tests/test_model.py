"""
Tests untuk validasi model: task, jumlah kelas, nama kelas.
"""
from __future__ import annotations


EXPECTED_CLASSES = sorted([
    "bacterial_leaf_blight",
    "bacterial_leaf_streak",
    "bacterial_panicle_blight",
    "blast",
    "brown_spot",
    "dead_heart",
    "downy_mildew",
    "hispa",
    "normal",
    "tungro",
])


def test_model_loaded(client):
    """Model harus dalam state loaded."""
    classifier = client.app.state.classifier
    assert classifier.is_loaded is True


def test_model_task_is_classify(client):
    """Model task harus 'classify'."""
    classifier = client.app.state.classifier
    info = classifier.get_model_info()
    assert info["task"] == "classify", f"task={info['task']} bukan classify"


def test_model_has_10_classes(client):
    """Model harus memiliki tepat 10 kelas."""
    classifier = client.app.state.classifier
    info = classifier.get_model_info()
    assert info["num_classes"] == 10, f"num_classes={info['num_classes']}"


def test_model_class_names_correct(client):
    """Nama kelas model harus sesuai 10 expected classes."""
    classifier = client.app.state.classifier
    info = classifier.get_model_info()
    actual = sorted(info["classes"])
    assert actual == EXPECTED_CLASSES, f"actual={actual}"


def test_model_info_endpoint(client):
    """GET /api/v1/ai/model/info mengembalikan info lengkap."""
    response = client.get("/api/v1/ai/model/info")
    assert response.status_code == 200
    data = response.json()
    assert data["task"] == "classify"
    assert data["num_classes"] == 10
    assert "imgsz" in data
    assert "device" in data
    assert "name" in data
    assert "version" in data
    # Filesystem path tidak diekspos
    assert "path" not in data
    assert "model_path" not in data


def test_model_version_in_info(client):
    """Model version harus ada di response."""
    response = client.get("/api/v1/ai/model/info")
    data = response.json()
    assert data["version"] != ""
    assert data["name"] != ""
