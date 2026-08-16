from io import BytesIO

from fastapi.testclient import TestClient
from PIL import Image

from app.core.config import get_settings
from app.main import create_app


class FakeClassifier:
    is_loaded = True
    model_version = "test"
    load_error = None

    def predict(self, image_rgb):
        return "blast", "Blast", 0.91


def _checkerboard_png() -> bytes:
    image = Image.new("RGB", (64, 64), "white")
    pixels = image.load()
    for x in range(64):
        for y in range(64):
            if (x + y) % 2 == 0:
                pixels[x, y] = (0, 0, 0)
    buffer = BytesIO()
    image.save(buffer, format="PNG")
    return buffer.getvalue()


def _flat_png() -> bytes:
    buffer = BytesIO()
    Image.new("RGB", (64, 64), (120, 120, 120)).save(buffer, format="PNG")
    return buffer.getvalue()


def test_detection_endpoint_success(monkeypatch):
    monkeypatch.setenv("MODEL_PATH", "missing-model.h5")
    get_settings.cache_clear()
    app = create_app()

    with TestClient(app) as client:
        app.state.container.disease_classifier = FakeClassifier()
        response = client.post(
            "/api/v1/diseases/detect",
            files={"image": ("leaf.png", _checkerboard_png(), "image/png")},
        )

    payload = response.json()
    assert response.status_code == 200
    assert payload["success"] is True
    assert payload["data"]["disease_code"] == "blast"


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
