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


def _green_leaf_png() -> bytes:
    """Membuat citra daun hijau bertekstur untuk pengujian validasi daun."""
    image = Image.new("RGB", (64, 64), (34, 139, 34))  # ForestGreen
    pixels = image.load()
    for x in range(64):
        for y in range(64):
            # Pola tekstur urat daun hijau dan kuning kecokelatan
            if (x + y) % 2 == 0:
                pixels[x, y] = (46, 139, 87)  # SeaGreen
            elif (x * y) % 5 == 0:
                pixels[x, y] = (154, 205, 50)  # YellowGreen
    buffer = BytesIO()
    image.save(buffer, format="PNG")
    return buffer.getvalue()


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


def _skin_png() -> bytes:
    """Membuat citra warna kulit untuk pengujian penolakan wajah/manusia."""
    buffer = BytesIO()
    Image.new("RGB", (64, 64), (235, 180, 150)).save(buffer, format="PNG")
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
