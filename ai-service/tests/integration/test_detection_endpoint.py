from io import BytesIO

import cv2
import numpy as np
from fastapi.testclient import TestClient

from app.core.config import get_settings
from app.main import create_app


class FakeClassifier:
    is_loaded = True
    model_version = "test"
    load_error = None

    def predict_top(self, image_rgb, top_k=3):
        return (
            "blast",
            "Blast",
            0.91,
            [
                {"disease_code": "blast", "disease_name": "Blast", "confidence": 0.91},
                {"disease_code": "brown_spot", "disease_name": "Brown Spot", "confidence": 0.07},
            ],
            0.84,
        )


def _green_leaf_png() -> bytes:
    """Membuat citra daun hijau bertekstur untuk pengujian validasi daun."""
    image = np.full((64, 64, 3), (34, 139, 34), dtype=np.uint8)
    for x in range(64):
        for y in range(64):
            # Pola tekstur urat daun hijau dan kuning kecokelatan
            if (x + y) % 2 == 0:
                image[y, x] = (46, 139, 87)  # SeaGreen
            elif (x * y) % 5 == 0:
                image[y, x] = (154, 205, 50)  # YellowGreen
    return _png_bytes(image)


def _checkerboard_png() -> bytes:
    image = np.full((64, 64, 3), 255, dtype=np.uint8)
    for x in range(64):
        for y in range(64):
            if (x + y) % 2 == 0:
                image[y, x] = (0, 0, 0)
    return _png_bytes(image)


def _flat_png() -> bytes:
    return _png_bytes(np.full((64, 64, 3), 120, dtype=np.uint8))


def _skin_png() -> bytes:
    """Membuat citra warna kulit untuk pengujian penolakan wajah/manusia."""
    image = np.full((64, 64, 3), (235, 180, 150), dtype=np.uint8)
    for x in range(64):
        for y in range(64):
            if (x + y) % 2 == 0:
                image[y, x] = (210, 155, 125)
    return _png_bytes(image)


def _png_bytes(image_rgb: np.ndarray) -> bytes:
    ok, encoded = cv2.imencode(".png", cv2.cvtColor(image_rgb, cv2.COLOR_RGB2BGR))
    if not ok:
        raise RuntimeError("Gagal membuat PNG test.")
    buffer = BytesIO(encoded.tobytes())
    return buffer.getvalue()


def test_detection_endpoint_success(monkeypatch):
    monkeypatch.setenv("MODEL_PATH", "missing-model.h5")
    get_settings.cache_clear()
    app = create_app()

    with TestClient(app) as client:
        app.state.container.disease_classifier = FakeClassifier()
        response = client.post(
            "/api/v1/diseases/detect",
            files={"image": ("leaf.png", _green_leaf_png(), "image/png")},
        )

    payload = response.json()
    assert response.status_code == 200
    assert payload["success"] is True
    assert payload["data"]["disease_code"] == "blast"
    assert payload["data"]["top_predictions"][0]["disease_code"] == "blast"
    assert payload["data"]["prediction_margin"] == 0.84


def test_detection_endpoint_rejects_empty_file(monkeypatch):
    monkeypatch.setenv("MODEL_PATH", "missing-model.h5")
    get_settings.cache_clear()
    app = create_app()

    with TestClient(app) as client:
        response = client.post(
            "/api/v1/diseases/detect",
            files={"image": ("leaf.png", b"", "image/png")},
        )

    assert response.status_code == 422
    assert response.json()["error"]["code"] == "EMPTY_IMAGE"


def test_detection_endpoint_rejects_unsupported_format(monkeypatch):
    monkeypatch.setenv("MODEL_PATH", "missing-model.h5")
    get_settings.cache_clear()
    app = create_app()

    with TestClient(app) as client:
        response = client.post(
            "/api/v1/diseases/detect",
            files={"image": ("leaf.gif", b"GIF89a", "image/gif")},
        )

    assert response.status_code == 422
    assert response.json()["error"]["code"] == "UNSUPPORTED_IMAGE_TYPE"


def test_detection_endpoint_rejects_blurry_image(monkeypatch):
    monkeypatch.setenv("MODEL_PATH", "missing-model.h5")
    get_settings.cache_clear()
    app = create_app()

    with TestClient(app) as client:
        response = client.post(
            "/api/v1/diseases/detect",
            files={"image": ("leaf.png", _flat_png(), "image/png")},
        )

    assert response.status_code == 422
    assert response.json()["error"]["code"] == "IMAGE_TOO_BLURRY"


def test_detection_endpoint_rejects_non_leaf_image(monkeypatch):
    monkeypatch.setenv("MODEL_PATH", "missing-model.h5")
    get_settings.cache_clear()
    app = create_app()

    with TestClient(app) as client:
        response = client.post(
            "/api/v1/diseases/detect",
            files={"image": ("not_leaf.png", _checkerboard_png(), "image/png")},
        )

    assert response.status_code == 422
    assert "IMAGE_NOT_LEAF" in response.json()["error"]["code"]


def test_detection_endpoint_rejects_skin_photo(monkeypatch):
    monkeypatch.setenv("MODEL_PATH", "missing-model.h5")
    get_settings.cache_clear()
    app = create_app()

    with TestClient(app) as client:
        response = client.post(
            "/api/v1/diseases/detect",
            files={"image": ("selfie.png", _skin_png(), "image/png")},
        )

    assert response.status_code == 422
    assert "IMAGE_NOT_LEAF" in response.json()["error"]["code"]
