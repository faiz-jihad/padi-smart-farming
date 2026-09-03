from __future__ import annotations

import io
import logging
from dataclasses import dataclass, field

import cv2
import numpy as np
from PIL import Image, ImageOps

logger = logging.getLogger(__name__)

ALLOWED_CONTENT_TYPES: frozenset[str] = frozenset(
    ["image/jpeg", "image/jpg", "image/png", "image/webp"]
)
ALLOWED_EXTENSIONS: frozenset[str] = frozenset([".jpg", ".jpeg", ".png", ".webp"])


@dataclass
class QualityReport:
    status: str  # "GOOD" | "LOW_QUALITY" | "INVALID"
    blur_score: float
    brightness: float
    width: int
    height: int
    issues: list[str] = field(default_factory=list)

    @property
    def is_valid(self) -> bool:
        return self.status != "INVALID"

    @property
    def is_good(self) -> bool:
        return self.status == "GOOD"


class ImageQualityService:
    """
    Layanan quality assessment gambar sebelum inference.

    Pipeline:
    1. Validasi format & ukuran file
    2. Decode & verify image (tidak korup)
    3. EXIF orientation fix
    4. Konversi ke RGB
    5. Hitung blur score, brightness, resolusi
    6. Klasifikasikan kualitas: GOOD | LOW_QUALITY | INVALID
    """

    def __init__(
        self,
        max_size_bytes: int = 10 * 1024 * 1024,
        blur_threshold: float = 50.0,
        brightness_min: float = 45.0,
        brightness_max: float = 225.0,
        min_width: int = 200,
        min_height: int = 200,
    ) -> None:
        self._max_size_bytes = max_size_bytes
        self._blur_threshold = blur_threshold
        self._brightness_min = brightness_min
        self._brightness_max = brightness_max
        self._min_width = min_width
        self._min_height = min_height

    def validate_upload(
        self,
        content: bytes,
        content_type: str | None,
        filename: str | None,
    ) -> None:
        """
        Validasi upload sebelum proses decode.
        Raise InvalidImageError / ImageTooLargeError / UnsupportedFormatError jika gagal.
        """
        from app.core.exceptions import (
            ImageTooLargeError,
            InvalidImageError,
            UnsupportedFormatError,
        )

        if not content:
            raise InvalidImageError("File kosong.")

        if len(content) > self._max_size_bytes:
            raise ImageTooLargeError(
                f"Ukuran file melebihi batas {self._max_size_bytes // (1024 * 1024)} MB."
            )

        # Cek magic bytes — bukan ekstensi saja
        if not self._is_supported_image_bytes(content):
            raise UnsupportedFormatError(
                "Format file tidak didukung. Gunakan JPG, PNG, atau WebP."
            )

    def decode_pil(self, content: bytes) -> Image.Image:
        """
        Decode bytes ke PIL Image RGB (canonical pipeline).
        - Validasi corrupt
        - Perbaiki EXIF orientation
        - Convert ke RGB
        - Validasi resolusi minimum
        Raise InvalidImageError jika gambar korup.
        """
        from app.core.exceptions import ImageTooSmallError, InvalidImageError

        try:
            pil_img = Image.open(io.BytesIO(content))
            pil_img.verify()  # Cek korup
        except Exception as exc:
            raise InvalidImageError(f"File tidak dapat dibaca sebagai gambar: {exc}") from exc

        try:
            pil_img = Image.open(io.BytesIO(content))  # Re-open setelah verify
            pil_img = ImageOps.exif_transpose(pil_img)
            pil_img = pil_img.convert("RGB")
        except Exception as exc:
            raise InvalidImageError(f"Gagal memproses gambar: {exc}") from exc

        w, h = pil_img.size
        if w < self._min_width or h < self._min_height:
            raise ImageTooSmallError(
                f"Resolusi gambar terlalu kecil: {w}x{h}. "
                f"Minimum: {self._min_width}x{self._min_height}."
            )

        return pil_img

    def decode_image(self, content: bytes) -> np.ndarray:
        """
        Decode bytes ke RGB numpy array (kompatibilitas).
        """
        pil_img = self.decode_pil(content)
        return np.array(pil_img, dtype=np.uint8)

    def assess_quality(self, image_input: np.ndarray | Image.Image | bytes) -> QualityReport:
        """
        Hitung blur score, brightness, dan klasifikasikan kualitas.
        Mendukung PIL Image, raw bytes, atau numpy array.
        """
        if isinstance(image_input, (bytes, bytearray)):
            pil_img = self.decode_pil(image_input)
            w, h = pil_img.size
            image_rgb = np.array(pil_img, dtype=np.uint8)
        elif isinstance(image_input, Image.Image):
            w, h = image_input.size
            image_rgb = np.array(image_input, dtype=np.uint8)
        elif isinstance(image_input, np.ndarray):
            h, w = image_input.shape[:2]
            image_rgb = image_input
        else:
            raise TypeError(f"Expected Image.Image, np.ndarray, or bytes, got {type(image_input)}")

        blur_score = self._compute_blur_score(image_rgb)
        brightness = self._compute_brightness(image_rgb)

        issues: list[str] = []
        is_blocking = False

        if w < self._min_width or h < self._min_height:
            issues.append("LOW_RESOLUTION")
            is_blocking = True

        if blur_score < self._blur_threshold / 2:
            # Sangat buram → blocking
            issues.append("TOO_BLURRY")
            is_blocking = True
        elif blur_score < self._blur_threshold:
            issues.append("TOO_BLURRY")

        if brightness < self._brightness_min / 2:
            issues.append("TOO_DARK")
            is_blocking = True
        elif brightness < self._brightness_min:
            issues.append("TOO_DARK")

        if brightness > self._brightness_max:
            issues.append("TOO_BRIGHT")

        if is_blocking:
            status = "INVALID"
        elif issues:
            status = "LOW_QUALITY"
        else:
            status = "GOOD"

        return QualityReport(
            status=status,
            blur_score=round(blur_score, 2),
            brightness=round(brightness, 2),
            width=w,
            height=h,
            issues=issues,
        )

    def _compute_blur_score(self, image_rgb: np.ndarray) -> float:
        """Variance of Laplacian — semakin tinggi semakin tajam."""
        try:
            gray = cv2.cvtColor(image_rgb, cv2.COLOR_RGB2GRAY)
            return float(cv2.Laplacian(gray, cv2.CV_64F).var())
        except Exception:
            return 0.0

    def _compute_brightness(self, image_rgb: np.ndarray) -> float:
        """Mean grayscale value (0-255)."""
        try:
            gray = cv2.cvtColor(image_rgb, cv2.COLOR_RGB2GRAY)
            return float(gray.mean())
        except Exception:
            return 0.0

    def _is_supported_image_bytes(self, content: bytes) -> bool:
        """Validasi berdasarkan magic bytes file, bukan ekstensi."""
        if len(content) < 4:
            return False
        # JPEG: FF D8 FF
        if content[:3] == b"\xff\xd8\xff":
            return True
        # PNG: 89 50 4E 47
        if content[:4] == b"\x89PNG":
            return True
        # WebP: RIFF....WEBP
        if content[:4] == b"RIFF" and content[8:12] == b"WEBP":
            return True
        return False
