"""
Tests untuk endpoint diagnosis dan decision engine.
"""
from __future__ import annotations

import io

import numpy as np
import pytest
from PIL import Image

from app.services.decision_engine import DecisionEngine
from app.services.image_quality import QualityReport
from app.services.padi_classifier import ClassifierResult, PredictionCandidate


# ─── Helper ───────────────────────────────────────────────────────────────────


def _make_classifier_result(
    class_name: str = "tungro",
    class_id: int = 9,
    confidence: float = 0.918,
    margin: float = 0.877,
    top2_class: str = "downy_mildew",
    top2_conf: float = 0.041,
) -> ClassifierResult:
    return ClassifierResult(
        request_id="test-id",
        class_id=class_id,
        class_name=class_name,
        confidence=confidence,
        confidence_percent=confidence * 100,
        margin=margin,
        top_predictions=[
            PredictionCandidate(rank=1, class_id=class_id, class_name=class_name, confidence=confidence),
            PredictionCandidate(rank=2, class_id=6, class_name=top2_class, confidence=top2_conf),
            PredictionCandidate(rank=3, class_id=3, class_name="blast", confidence=0.018),
        ],
        preprocessing_ms=5.0,
        inference_ms=42.0,
        total_latency_ms=47.0,
        model_name="paddy_doctor",
        model_version="v3",
        model_task="classify",
        model_imgsz=384,
        device="cpu",
    )


def _good_quality() -> QualityReport:
    return QualityReport(status="GOOD", blur_score=120.0, brightness=132.0, width=1920, height=1080)


def _low_quality() -> QualityReport:
    return QualityReport(status="LOW_QUALITY", blur_score=30.0, brightness=80.0, width=640, height=480, issues=["TOO_BLURRY"])


def _invalid_quality() -> QualityReport:
    return QualityReport(status="INVALID", blur_score=5.0, brightness=20.0, width=200, height=200, issues=["TOO_BLURRY", "TOO_DARK"])


# ─── Decision Engine Tests ────────────────────────────────────────────────────


def test_decision_retake_on_invalid_quality():
    """Kualitas INVALID harus menghasilkan RETAKE_PHOTO."""
    engine = DecisionEngine()
    result = engine.decide(_make_classifier_result(), _invalid_quality())
    assert result.status == "RETAKE_PHOTO"
    assert result.needs_retake is True


def test_decision_high_confidence_clear_case():
    """Confidence >= 0.85, margin >= 0.20, quality GOOD, no confusion → HIGH_CONFIDENCE."""
    engine = DecisionEngine()
    cr = _make_classifier_result(
        class_name="tungro", confidence=0.918, margin=0.877, top2_class="blast"
    )
    result = engine.decide(cr, _good_quality())
    # tungro + blast = bukan confusion pair
    assert result.status == "HIGH_CONFIDENCE"
    assert result.needs_ppl_review is False


def test_decision_need_review_confusion_pair():
    """Confidence tinggi tapi confusion pair downy_mildew/tungro → NEED_PPL_REVIEW."""
    engine = DecisionEngine()
    cr = _make_classifier_result(
        class_name="downy_mildew",
        class_id=6,
        confidence=0.91,
        margin=0.87,
        top2_class="tungro",
        top2_conf=0.04,
    )
    result = engine.decide(cr, _good_quality())
    assert result.status == "NEED_PPL_REVIEW"
    assert result.needs_differential_review is True


def test_decision_uncertain_low_confidence():
    """Confidence < 0.60 → UNCERTAIN."""
    engine = DecisionEngine()
    cr = _make_classifier_result(confidence=0.35, margin=0.15)
    result = engine.decide(cr, _good_quality())
    assert result.status == "UNCERTAIN"
    assert result.needs_ppl_review is True


def test_decision_need_review_medium_confidence():
    """Confidence 0.60-0.84 → NEED_PPL_REVIEW."""
    engine = DecisionEngine()
    cr = _make_classifier_result(confidence=0.72, margin=0.30, top2_class="blast")
    result = engine.decide(cr, _good_quality())
    assert result.status == "NEED_PPL_REVIEW"


def test_decision_small_margin_forces_review():
    """Confidence tinggi tapi margin kecil → NEED_PPL_REVIEW."""
    engine = DecisionEngine()
    cr = _make_classifier_result(confidence=0.90, margin=0.05, top2_class="brown_spot")
    result = engine.decide(cr, _good_quality())
    assert result.status == "NEED_PPL_REVIEW"


# ─── Diagnosis Endpoint Tests ─────────────────────────────────────────────────


def test_diagnose_valid_jpeg(client, sample_jpeg_bytes):
    """POST dengan JPEG valid harus mengembalikan response sukses."""
    response = client.post(
        "/api/v1/ai/padi/diagnose",
        files={"image": ("leaf.jpg", sample_jpeg_bytes, "image/jpeg")},
        data={"locale": "id"},
    )
    assert response.status_code == 200
    data = response.json()
    assert data["success"] is True
    assert "data" in data


def test_diagnose_response_schema_complete(client, sample_jpeg_bytes):
    """Response harus memiliki semua field yang diperlukan Flutter."""
    response = client.post(
        "/api/v1/ai/padi/diagnose",
        files={"image": ("leaf.jpg", sample_jpeg_bytes, "image/jpeg")},
    )
    assert response.status_code == 200
    d = response.json()["data"]

    # Top-level
    assert "request_id" in d
    assert "diagnosis_id" in d
    assert "model" in d
    assert "prediction" in d
    assert "decision" in d
    assert "quality" in d
    assert "disease" in d
    assert "latency_ms" in d

    # Model
    assert d["model"]["task"] == "classify"
    assert "version" in d["model"]

    # Prediction
    pred = d["prediction"]
    assert "class_name" in pred
    assert "confidence" in pred
    assert "confidence_percent" in pred
    assert "top_predictions" in pred
    assert "margin" in pred

    # Decision
    dec = d["decision"]
    assert "status" in dec
    assert "needs_ppl_review" in dec
    assert "needs_retake" in dec
    assert "needs_differential_review" in dec

    # Quality
    q = d["quality"]
    assert "status" in q
    assert "blur_score" in q
    assert "brightness" in q


def test_diagnose_top3_sorted_descending(client, sample_jpeg_bytes):
    """Top predictions dari response harus diurutkan descending (atau kosong jika RETAKE_PHOTO)."""
    response = client.post(
        "/api/v1/ai/padi/diagnose",
        files={"image": ("leaf.jpg", sample_jpeg_bytes, "image/jpeg")},
    )
    assert response.status_code == 200
    data = response.json()["data"]
    decision_status = data["decision"]["status"]
    top = data["prediction"]["top_predictions"]

    if decision_status == "RETAKE_PHOTO":
        # Kualitas terlalu buruk, tidak ada prediksi — ini expected
        return

    # Ada prediksi
    assert len(top) >= 1
    if len(top) >= 2:
        confs = [p["confidence"] for p in top]
        assert confs == sorted(confs, reverse=True), f"Not sorted: {confs}"


def test_diagnose_confidence_in_range(client, sample_jpeg_bytes):
    """Confidence harus antara 0 dan 1."""
    response = client.post(
        "/api/v1/ai/padi/diagnose",
        files={"image": ("leaf.jpg", sample_jpeg_bytes, "image/jpeg")},
    )
    data = response.json()["data"]["prediction"]
    assert 0.0 <= data["confidence"] <= 1.0


def test_diagnose_margin_in_range(client, sample_jpeg_bytes):
    """Margin harus antara 0 dan 1."""
    response = client.post(
        "/api/v1/ai/padi/diagnose",
        files={"image": ("leaf.jpg", sample_jpeg_bytes, "image/jpeg")},
    )
    margin = response.json()["data"]["prediction"]["margin"]
    assert 0.0 <= margin <= 1.0


def test_diagnose_invalid_file_rejected(client, corrupt_bytes):
    """File korup harus menghasilkan error response."""
    response = client.post(
        "/api/v1/ai/padi/diagnose",
        files={"image": ("corrupt.jpg", corrupt_bytes, "image/jpeg")},
    )
    assert response.status_code in (400, 413, 415, 422)
    data = response.json()
    assert data.get("success") is False
    assert "error" in data


def test_diagnose_large_file_rejected(client, large_image_bytes):
    """File melebihi 10MB harus ditolak."""
    large_svc = client.app.state.quality_service
    # Override size limit temporarily
    original = large_svc._max_size_bytes
    large_svc._max_size_bytes = 1 * 1024 * 1024  # 1MB
    try:
        response = client.post(
            "/api/v1/ai/padi/diagnose",
            files={"image": ("large.png", large_image_bytes, "image/png")},
        )
        assert response.status_code in (400, 413, 415, 422)
    finally:
        large_svc._max_size_bytes = original


def test_diagnose_normal_no_certainty_claim(client, sample_jpeg_bytes):
    """
    Jika prediksi Normal, response tidak boleh mengklaim tanaman 'pasti sehat'.
    """
    from unittest.mock import AsyncMock, MagicMock
    from app.services.padi_classifier import ClassifierResult, PredictionCandidate

    normal_result = ClassifierResult(
        request_id="test-normal",
        class_id=8,
        class_name="normal",
        confidence=0.91,
        confidence_percent=91.0,
        margin=0.88,
        top_predictions=[
            PredictionCandidate(rank=1, class_id=8, class_name="normal", confidence=0.91),
            PredictionCandidate(rank=2, class_id=7, class_name="hispa", confidence=0.03),
        ],
        preprocessing_ms=5.0,
        inference_ms=40.0,
        total_latency_ms=45.0,
        model_name="paddy_doctor",
        model_version="v3",
        model_task="classify",
        model_imgsz=384,
        device="cpu",
    )

    original = client.app.state.classifier.classify

    async def mock_normal(image_rgb):
        return normal_result

    client.app.state.classifier.classify = mock_normal

    try:
        response = client.post(
            "/api/v1/ai/padi/diagnose",
            files={"image": ("leaf.jpg", sample_jpeg_bytes, "image/jpeg")},
            data={"locale": "id"},
        )
        assert response.status_code == 200
        disease = response.json()["data"]["disease"]
        description = disease.get("description", "")
        # Tidak boleh ada klaim "pasti sehat"
        assert "pasti sehat" not in description.lower()
        assert "guaranteed healthy" not in description.lower()
        # Harus menyebut "tidak ditemukan" atau "no dominant"
        assert (
            "tidak ditemukan" in description.lower()
            or "no dominant" in description.lower()
            or "pola penyakit" in description.lower()
        )
    finally:
        client.app.state.classifier.classify = original


def test_diagnose_concurrent_no_model_reload(client, sample_jpeg_bytes):
    """
    Concurrent request tidak boleh menyebabkan model reload.
    Classifier adalah singleton — state.classifier harus tetap objek yang sama.
    """
    import threading

    original_classifier = client.app.state.classifier
    results = []

    def make_request():
        r = client.post(
            "/api/v1/ai/padi/diagnose",
            files={"image": ("leaf.jpg", sample_jpeg_bytes, "image/jpeg")},
        )
        results.append(r.status_code)

    threads = [threading.Thread(target=make_request) for _ in range(5)]
    for t in threads:
        t.start()
    for t in threads:
        t.join()

    # Semua request berhasil
    assert all(code == 200 for code in results), f"results={results}"
    # Classifier masih objek yang sama
    assert client.app.state.classifier is original_classifier
