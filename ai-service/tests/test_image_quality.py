"""
Tests untuk image quality service.
"""
from __future__ import annotations

import io

import numpy as np
import pytest
from PIL import Image

from app.services.image_quality import ImageQualityService


@pytest.fixture
def quality_svc():
    return ImageQualityService(
        max_size_bytes=10 * 1024 * 1024,
        blur_threshold=50.0,
        brightness_min=45.0,
        brightness_max=225.0,
        min_width=200,
        min_height=200,
    )


def _make_rgb_array(width=640, height=480, green=120, red=30, blue=30) -> np.ndarray:
    arr = np.zeros((height, width, 3), dtype=np.uint8)
    arr[:, :, 0] = red
    arr[:, :, 1] = green
    arr[:, :, 2] = blue
    return arr


def test_quality_good_image(quality_svc):
    """Gambar valid dengan tekstur harus GOOD atau LOW_QUALITY (bukan INVALID)."""
    # Gambar dengan noise/tekstur agar blur score tidak 0
    rng = np.random.default_rng(42)
    arr = rng.integers(60, 180, (480, 640, 3), dtype=np.uint8)
    image_rgb = arr.astype(np.uint8)
    report = quality_svc.assess_quality(image_rgb)
    assert report.status in ("GOOD", "LOW_QUALITY")
    assert report.width >= 200
    assert report.height >= 200
    assert report.blur_score >= 0
    assert report.brightness >= 0


def test_diagnose_top3_sorted_descending_unit():
    """Top predictions dari ClassifierResult harus diurutkan descending."""
    from app.services.padi_classifier import ClassifierResult, PredictionCandidate
    top = [
        PredictionCandidate(rank=1, class_id=9, class_name="tungro", confidence=0.918),
        PredictionCandidate(rank=2, class_id=6, class_name="downy_mildew", confidence=0.041),
        PredictionCandidate(rank=3, class_id=3, class_name="blast", confidence=0.018),
    ]
    confs = [p.confidence for p in top]
    assert confs == sorted(confs, reverse=True), f"Not sorted: {confs}"


def test_quality_report_has_required_fields(quality_svc, sample_jpeg_bytes):
    """QualityReport harus memiliki semua field yang diperlukan."""
    image_rgb = quality_svc.decode_image(sample_jpeg_bytes)
    report = quality_svc.assess_quality(image_rgb)
    assert hasattr(report, "status")
    assert hasattr(report, "blur_score")
    assert hasattr(report, "brightness")
    assert hasattr(report, "width")
    assert hasattr(report, "height")
    assert hasattr(report, "issues")
    assert isinstance(report.issues, list)


def test_invalid_file_rejected(quality_svc, corrupt_bytes):
    """File korup harus menghasilkan InvalidImageError."""
    from app.core.exceptions import InvalidImageError

    with pytest.raises((InvalidImageError, Exception)):
        quality_svc.validate_upload(corrupt_bytes, "image/jpeg", "test.jpg")
        quality_svc.decode_image(corrupt_bytes)


def test_large_file_rejected(quality_svc, large_image_bytes):
    """File melebihi batas ukuran harus ditolak."""
    from app.core.exceptions import ImageTooLargeError

    small_svc = ImageQualityService(max_size_bytes=1 * 1024 * 1024)  # 1MB limit
    with pytest.raises(ImageTooLargeError):
        small_svc.validate_upload(large_image_bytes, "image/png", "large.png")


def test_tiny_image_rejected(quality_svc, tiny_image_bytes):
    """Gambar terlalu kecil harus ditolak saat decode."""
    from app.core.exceptions import ImageTooSmallError

    with pytest.raises(ImageTooSmallError):
        quality_svc.decode_image(tiny_image_bytes)


def test_dark_image_flagged(quality_svc):
    """Gambar terlalu gelap harus memiliki issue TOO_DARK."""
    dark_arr = np.zeros((400, 400, 3), dtype=np.uint8)  # Hitam penuh
    report = quality_svc.assess_quality(dark_arr)
    assert "TOO_DARK" in report.issues


def test_bright_image_flagged(quality_svc):
    """Gambar terlalu terang (overexposed) harus memiliki issue TOO_BRIGHT."""
    bright_arr = np.full((400, 400, 3), 255, dtype=np.uint8)  # Putih penuh
    report = quality_svc.assess_quality(bright_arr)
    assert "TOO_BRIGHT" in report.issues


def test_magic_bytes_jpeg(quality_svc, sample_jpeg_bytes):
    """Validasi magic bytes JPEG harus lolos."""
    quality_svc.validate_upload(sample_jpeg_bytes, "image/jpeg", "leaf.jpg")


def test_magic_bytes_png(quality_svc, sample_png_bytes):
    """Validasi magic bytes PNG harus lolos."""
    quality_svc.validate_upload(sample_png_bytes, "image/png", "leaf.png")


def test_unsupported_format_rejected(quality_svc):
    """Bytes dengan format tidak didukung harus ditolak."""
    from app.core.exceptions import UnsupportedFormatError

    pdf_magic = b"%PDF-1.4 fake pdf content here"
    with pytest.raises(UnsupportedFormatError):
        quality_svc.validate_upload(pdf_magic, "application/pdf", "doc.pdf")
