"""
Pytest conftest untuk P.A.D.I. AI Service tests.

Fixtures:
- client: TestClient dengan mock model (tidak memerlukan GPU atau model .pt)
- sample_jpeg_bytes: gambar JPEG sintetis yang valid
- sample_png_bytes: gambar PNG sintetis yang valid
- corrupt_bytes: bytes yang tidak valid sebagai gambar
- large_image_bytes: gambar yang melebihi batas ukuran
"""
from __future__ import annotations

import io
import os
import sys
from pathlib import Path
from unittest.mock import MagicMock, patch

import numpy as np
import pytest
from fastapi.testclient import TestClient
from PIL import Image

# Pastikan ai-service root ada di sys.path
sys.path.insert(0, str(Path(__file__).resolve().parents[1]))


def _make_jpeg_bytes(width: int = 640, height: int = 480) -> bytes:
    """Buat gambar JPEG sintetis bertekstur (simulasi daun padi dengan gradasi dan noise)."""
    rng = np.random.default_rng(42)
    # Background hijau daun
    arr = np.zeros((height, width, 3), dtype=np.uint8)
    arr[:, :, 1] = rng.integers(100, 160, (height, width), dtype=np.uint8)  # Channel hijau
    arr[:, :, 0] = rng.integers(40, 80, (height, width), dtype=np.uint8)    # Channel merah
    arr[:, :, 2] = rng.integers(20, 60, (height, width), dtype=np.uint8)    # Channel biru
    # Tambahkan garis vena daun horizontal dan vertikal
    for i in range(0, height, 40):
        arr[i:i+3, :, 1] = 180
    for j in range(0, width, 50):
        arr[:, j:j+2, 0] = 70
    img = Image.fromarray(arr, mode="RGB")
    buf = io.BytesIO()
    img.save(buf, format="JPEG", quality=90)
    return buf.getvalue()


def _make_png_bytes(width: int = 400, height: int = 400) -> bytes:
    rng = np.random.default_rng(42)
    arr = np.zeros((height, width, 3), dtype=np.uint8)
    arr[:, :, 1] = rng.integers(90, 150, (height, width), dtype=np.uint8)
    arr[:, :, 0] = rng.integers(40, 70, (height, width), dtype=np.uint8)
    arr[:, :, 2] = rng.integers(20, 50, (height, width), dtype=np.uint8)
    img = Image.fromarray(arr, mode="RGB")
    buf = io.BytesIO()
    img.save(buf, format="PNG")
    return buf.getvalue()



@pytest.fixture(scope="session")
def sample_jpeg_bytes() -> bytes:
    return _make_jpeg_bytes()


@pytest.fixture(scope="session")
def sample_png_bytes() -> bytes:
    return _make_png_bytes()


@pytest.fixture(scope="session")
def corrupt_bytes() -> bytes:
    return b"not_an_image_random_garbage_data_12345"


@pytest.fixture(scope="session")
def large_image_bytes() -> bytes:
    """Gambar yang valid tapi ukuran bytes > 10MB."""
    arr = np.random.randint(0, 255, (2000, 2000, 3), dtype=np.uint8)
    img = Image.fromarray(arr, mode="RGB")
    buf = io.BytesIO()
    # Simpan tanpa kompresi untuk memastikan besar
    img.save(buf, format="PNG", compress_level=0)
    return buf.getvalue()


@pytest.fixture(scope="session")
def tiny_image_bytes() -> bytes:
    """Gambar yang terlalu kecil (50x50 px)."""
    arr = np.zeros((50, 50, 3), dtype=np.uint8)
    arr[:, :, 1] = 80
    img = Image.fromarray(arr, mode="RGB")
    buf = io.BytesIO()
    img.save(buf, format="JPEG")
    return buf.getvalue()


def _make_mock_classifier():
    """Buat mock PADIClassifier yang tidak memerlukan model .pt."""
    from app.services.padi_classifier import ClassifierResult, PredictionCandidate

    mock = MagicMock()
    mock.is_loaded = True
    mock.device = "cpu"
    mock.model_version = "v3"
    mock.model_name = "paddy_doctor"
    mock.model_imgsz = 384

    mock_result = ClassifierResult(
        request_id="test-request-id",
        class_id=9,
        class_name="tungro",
        confidence=0.918,
        confidence_percent=91.8,
        margin=0.877,
        top_predictions=[
            PredictionCandidate(rank=1, class_id=9, class_name="tungro", confidence=0.918),
            PredictionCandidate(rank=2, class_id=6, class_name="downy_mildew", confidence=0.041),
            PredictionCandidate(rank=3, class_id=3, class_name="blast", confidence=0.018),
        ],
        preprocessing_ms=5.2,
        inference_ms=42.1,
        total_latency_ms=47.3,
        model_name="paddy_doctor",
        model_version="v3",
        model_task="classify",
        model_imgsz=384,
        device="cpu",
    )

    async def mock_classify(image_rgb):
        return mock_result

    mock.classify = mock_classify
    mock.get_model_info.return_value = {
        "name": "paddy_doctor",
        "version": "v3",
        "full_name": "paddy_doctor_v3",
        "task": "classify",
        "classes": [
            "bacterial_leaf_blight", "bacterial_leaf_streak", "bacterial_panicle_blight",
            "blast", "brown_spot", "dead_heart", "downy_mildew", "hispa", "normal", "tungro",
        ],
        "num_classes": 10,
        "imgsz": 384,
        "device": "cpu",
        "loaded": True,
    }
    return mock


@pytest.fixture(scope="session")
def client(tmp_path_factory) -> TestClient:
    """TestClient dengan mocked PADIClassifier."""
    from app.core.config import get_settings
    from app.services.decision_engine import DecisionEngine
    from app.services.disease_catalog import DiseaseCatalog
    from app.services.image_quality import ImageQualityService

    # Set env agar .env tidak mengganggu test
    os.environ.setdefault("PADI_MODEL_VERSION", "v3")
    os.environ.setdefault("PADI_MODEL_NAME", "paddy_doctor")

    # Patch lifespan agar tidak load model sungguhan
    mock_classifier = _make_mock_classifier()

    from app.main import create_app

    # Override lifespan via patch
    from contextlib import asynccontextmanager
    from fastapi import FastAPI

    @asynccontextmanager
    async def test_lifespan(app: FastAPI):
        settings = get_settings()
        app.state.classifier = mock_classifier
        app.state.quality_service = ImageQualityService(
            max_size_bytes=10 * 1024 * 1024,
            blur_threshold=50.0,
            brightness_min=45.0,
            brightness_max=225.0,
            min_width=200,
            min_height=200,
        )
        app.state.decision_engine = DecisionEngine(
            high_confidence_threshold=0.85,
            review_confidence_threshold=0.60,
            min_margin_threshold=0.20,
        )

        # Cari catalog path
        base = Path(__file__).resolve().parents[1]
        catalog_path = base / "data" / "disease_catalog.json"
        app.state.disease_catalog = DiseaseCatalog(catalog_path=catalog_path)
        yield

    with patch("app.main.lifespan", test_lifespan):
        test_app = create_app()
        # Override lifespan setelah create
        test_app.router.lifespan_context = test_lifespan

    # Inject state langsung
    from fastapi.testclient import TestClient as _TC
    tc = _TC(test_app)

    # Inject state sebelum startup
    settings = get_settings()
    base = Path(__file__).resolve().parents[1]
    catalog_path = base / "data" / "disease_catalog.json"

    test_app.state.classifier = mock_classifier
    test_app.state.quality_service = ImageQualityService(
        max_size_bytes=10 * 1024 * 1024,
        blur_threshold=50.0,
        brightness_min=45.0,
        brightness_max=225.0,
        min_width=200,
        min_height=200,
    )
    test_app.state.decision_engine = DecisionEngine(
        high_confidence_threshold=0.85,
        review_confidence_threshold=0.60,
        min_margin_threshold=0.20,
    )
    test_app.state.disease_catalog = DiseaseCatalog(catalog_path=catalog_path)

    return tc
