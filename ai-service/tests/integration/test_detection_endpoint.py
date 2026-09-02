from io import BytesIO

import cv2
import numpy as np
from fastapi.testclient import TestClient

from app.main import create_app


def _green_leaf_png() -> bytes:
    """Membuat citra daun hijau bertekstur (simulasi daun bertekstur)."""
    rng = np.random.default_rng(42)
    image = np.zeros((480, 640, 3), dtype=np.uint8)
    image[:, :, 1] = rng.integers(100, 160, (480, 640), dtype=np.uint8)
    image[:, :, 0] = rng.integers(40, 80, (480, 640), dtype=np.uint8)
    image[:, :, 2] = rng.integers(20, 60, (480, 640), dtype=np.uint8)
    for i in range(0, 480, 40):
        image[i:i+3, :, 1] = 180
    for j in range(0, 640, 50):
        image[:, j:j+2, 0] = 70
    return _png_bytes(image)


def _flat_png() -> bytes:
    """Gambar datar tanpa tekstur (blur score = 0)."""
    return _png_bytes(np.full((300, 300, 3), 120, dtype=np.uint8))


def _png_bytes(image_rgb: np.ndarray) -> bytes:
    ok, encoded = cv2.imencode(".png", cv2.cvtColor(image_rgb, cv2.COLOR_RGB2BGR))
    if not ok:
        raise RuntimeError("Gagal membuat PNG test.")
    return BytesIO(encoded.tobytes()).getvalue()


def test_detection_endpoint_success():
    app = create_app()
    with TestClient(app) as client:
        response = client.post(
            "/api/v1/diseases/detect",
            files={"image": ("leaf.png", _green_leaf_png(), "image/png")},
        )

    assert response.status_code == 200
    payload = response.json()
    assert payload["success"] is True
    assert "disease_code" in payload["data"]
    assert "confidence" in payload["data"]
    assert "top_predictions" in payload["data"]


def test_detection_endpoint_rejects_empty_file():
    app = create_app()
    with TestClient(app) as client:
        response = client.post(
            "/api/v1/diseases/detect",
            files={"image": ("leaf.png", b"", "image/png")},
        )

    assert response.status_code == 422
    assert response.json()["error"]["code"] in ("EMPTY_IMAGE", "INVALID_IMAGE")


def test_detection_endpoint_rejects_unsupported_format():
    app = create_app()
    with TestClient(app) as client:
        response = client.post(
            "/api/v1/diseases/detect",
            files={"image": ("doc.pdf", b"%PDF-1.4 fake pdf content", "application/pdf")},
        )

    assert response.status_code in (415, 422)


def test_detection_endpoint_rejects_blurry_image():
    app = create_app()
    with TestClient(app) as client:
        response = client.post(
            "/api/v1/diseases/detect",
            files={"image": ("leaf.png", _flat_png(), "image/png")},
        )

    assert response.status_code == 422
    assert response.json()["error"]["code"] == "INVALID_IMAGE"
