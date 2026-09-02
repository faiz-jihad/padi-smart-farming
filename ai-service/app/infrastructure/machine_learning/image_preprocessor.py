from __future__ import annotations

import io
import logging

import cv2
import numpy as np

from app.core.exceptions import ImageValidationError

logger = logging.getLogger(__name__)

# Konstanta EXIF Orientation tag
_EXIF_ORIENTATION_TAG = 0x0112
_EXIF_ORIENTATION_TRANSFORMS = {
    2: cv2.ROTATE_180,  # Mirrored horizontal
    3: cv2.ROTATE_180,  # Rotated 180
    4: None,            # Mirrored vertical
    5: cv2.ROTATE_90_CLOCKWISE,   # Mirrored horizontal + 270
    6: cv2.ROTATE_90_CLOCKWISE,   # Rotated 270 CCW (90 CW)
    7: cv2.ROTATE_90_COUNTERCLOCKWISE,  # Mirrored horizontal + 90
    8: cv2.ROTATE_90_COUNTERCLOCKWISE,  # Rotated 90 CCW
}


class ImagePreprocessor:
    """Preprocessing pipeline gambar untuk inferensi model MobileNetV2 penyakit padi.

    Normalisasi wajib: (pixel / 127.5) - 1.0  →  range [-1.0, 1.0]
    Ini adalah normalisasi RESMI MobileNetV2/MobileNetV3 Keras.
    JANGAN ganti ke /255.0 karena akan merusak akurasi model.
    """

    def __init__(self, target_size: tuple[int, int] = (224, 224)) -> None:
        self.target_size = target_size

    # ------------------------------------------------------------------
    # DECODE dengan EXIF Orientation Fix
    # ------------------------------------------------------------------

    def decode(self, content: bytes) -> np.ndarray:
        """Decode byte gambar menjadi array RGB dengan EXIF orientation fix.

        Kamera Android/iOS sering menyimpan gambar dalam orientasi fisik sensor
        (landscape) namun menambahkan EXIF Orientation tag untuk koreksi tampilan.
        OpenCV cv2.imdecode() MENGABAIKAN tag EXIF ini, sehingga gambar portrait
        dari HP bisa masuk ke model dalam keadaan miring 90°.

        Fix ini membaca EXIF melalui Pillow (yang support EXIF) kemudian
        merotasi gambar sebelum dikonversi ke numpy array.
        """
        try:
            from PIL import Image as PilImage

            pil_img = PilImage.open(io.BytesIO(content))

            # Dapatkan EXIF orientation jika ada
            exif_data = None
            try:
                exif_data = pil_img._getexif()  # type: ignore[attr-defined]
            except (AttributeError, Exception):
                pass

            if exif_data:
                orientation = exif_data.get(_EXIF_ORIENTATION_TAG)
                if orientation and orientation in (2, 3, 4, 5, 6, 7, 8):
                    try:
                        # Pillow ImageOps.exif_transpose adalah cara paling andal
                        from PIL import ImageOps
                        pil_img = ImageOps.exif_transpose(pil_img)
                        logger.debug(
                            "event=exif_orientation_corrected orientation=%d", orientation
                        )
                    except Exception as exc:
                        logger.debug("event=exif_transpose_failed error=%s", exc)

            # Konversi ke RGB (handle RGBA, grayscale, palette, dll)
            if pil_img.mode != "RGB":
                pil_img = pil_img.convert("RGB")

            return np.array(pil_img, dtype=np.uint8)

        except ImageValidationError:
            raise
        except Exception:
            # Fallback ke OpenCV jika Pillow gagal (file bukan JPEG/PNG valid)
            image_array = np.frombuffer(content, dtype=np.uint8)
            decoded_image = cv2.imdecode(image_array, cv2.IMREAD_COLOR)
            if decoded_image is None:
                raise ImageValidationError("Gambar tidak dapat dibaca.", code="INVALID_IMAGE") from None
            return cv2.cvtColor(decoded_image, cv2.COLOR_BGR2RGB)

    # ------------------------------------------------------------------
    # IMAGE QUALITY MEASUREMENT
    # ------------------------------------------------------------------

    def measure_quality(self, image_rgb: np.ndarray) -> tuple[float, float]:
        """Mengukur blur dan brightness.

        Blur diukur dengan variance of Laplacian — angka tinggi = tajam.
        Brightness diukur dengan mean grayscale intensity (0–255).
        """
        grayscale_image = cv2.cvtColor(image_rgb, cv2.COLOR_RGB2GRAY)
        blur_score = float(cv2.Laplacian(grayscale_image, cv2.CV_64F).var())
        brightness_score = float(grayscale_image.mean())
        return blur_score, brightness_score

    def get_resolution(self, image_rgb: np.ndarray) -> tuple[int, int]:
        """Mengembalikan (height, width) dari gambar."""
        return image_rgb.shape[0], image_rgb.shape[1]

    # ------------------------------------------------------------------
    # LEAF FEATURE ANALYSIS
    # ------------------------------------------------------------------

    def analyze_leaf_features(self, image_rgb: np.ndarray) -> dict[str, float]:
        """Menganalisis karakteristik visual untuk memastikan objek adalah daun tanaman padi.

        Warna daun padi sehat: hijau (HSV hue 25–95).
        Warna daun padi berpenyakit: kuning/cokelat/hawar (HSV hue 8–35).
        Warna jerami/batan kering: straw (HSV hue 15–35, saturation rendah).
        """
        total_pixels = max(1, image_rgb.shape[0] * image_rgb.shape[1])
        hsv = cv2.cvtColor(image_rgb, cv2.COLOR_RGB2HSV)

        # 1. Spektrum hijau daun sehat (lebih luas untuk menutup variasi pencahayaan lapangan)
        green_mask = cv2.inRange(
            hsv,
            np.array([22, 20, 20], dtype=np.uint8),
            np.array([100, 255, 255], dtype=np.uint8),
        )
        # 2. Spektrum kuning/cokelat daun berpenyakit (lesi, hawar, blast, bercak)
        yellow_brown_mask = cv2.inRange(
            hsv,
            np.array([6, 25, 25], dtype=np.uint8),
            np.array([28, 255, 255], dtype=np.uint8),
        )
        # 3. Spektrum cokelat tua/hitam — lesi blast lanjut, dead heart
        dark_brown_mask = cv2.inRange(
            hsv,
            np.array([0, 15, 20], dtype=np.uint8),
            np.array([20, 180, 140], dtype=np.uint8),
        )
        # 4. Jerami/batan kering
        straw_mask = cv2.inRange(
            hsv,
            np.array([15, 15, 30], dtype=np.uint8),
            np.array([38, 200, 230], dtype=np.uint8),
        )

        # 5. Spektrum warna kulit manusia (wajah, selfie, tangan) — YCrCb space lebih akurat
        ycrcb = cv2.cvtColor(image_rgb, cv2.COLOR_RGB2YCrCb)
        skin_mask = cv2.inRange(
            ycrcb,
            np.array([0, 133, 77], dtype=np.uint8),
            np.array([255, 173, 127], dtype=np.uint8),
        )
        skin_ratio = float(cv2.countNonZero(skin_mask) / total_pixels)

        # KOREKSI PENTING: Jangan hitung piksel kulit manusia sebagai daun kuning/cokelat
        non_skin_mask = cv2.bitwise_not(skin_mask)
        yellow_brown_clean = cv2.bitwise_and(yellow_brown_mask, non_skin_mask)
        dark_brown_clean = cv2.bitwise_and(dark_brown_mask, non_skin_mask)

        leaf_mask = cv2.bitwise_or(
            green_mask,
            cv2.bitwise_or(yellow_brown_clean, cv2.bitwise_or(dark_brown_clean, straw_mask)),
        )

        kernel = np.ones((3, 3), np.uint8)
        leaf_mask = cv2.morphologyEx(leaf_mask, cv2.MORPH_CLOSE, kernel)

        green_ratio = float(cv2.countNonZero(green_mask) / total_pixels)
        yellow_brown_ratio = float(cv2.countNonZero(yellow_brown_clean) / total_pixels)
        leaf_ratio = float(cv2.countNonZero(leaf_mask) / total_pixels)

        # 6. Spektrum warna sintetis buatan (biru/cyan/ungu dominan — layar, dokumen, dinding cat)
        unnatural_mask = cv2.inRange(
            hsv,
            np.array([96, 40, 40], dtype=np.uint8),
            np.array([170, 255, 255], dtype=np.uint8),
        )
        unnatural_ratio = float(cv2.countNonZero(unnatural_mask) / total_pixels)

        # 7. Rata-rata saturasi (mendeteksi monokrom/kertas/semen abu-abu)
        mean_saturation = float(np.mean(hsv[:, :, 1]))

        return {
            "leaf_ratio": round(leaf_ratio, 4),
            "green_ratio": round(green_ratio, 4),
            "yellow_brown_ratio": round(yellow_brown_ratio, 4),
            "skin_ratio": round(skin_ratio, 4),
            "unnatural_ratio": round(unnatural_ratio, 4),
            "mean_saturation": round(mean_saturation, 2),
        }

    # ------------------------------------------------------------------
    # PREPROCESSING UNTUK MODEL — WAJIB KONSISTEN DENGAN TRAINING
    # ------------------------------------------------------------------

    def preprocess_for_model(self, image_rgb: np.ndarray) -> np.ndarray:
        """Preprocessing gambar untuk model MobileNetV2.

        Langkah-langkah:
        1. Letterbox resize — mempertahankan aspect ratio dengan padding abu-abu
           agar morfologi lesi tidak terdistorsi akibat stretching.
        2. Normalisasi MobileNetV2 resmi: (pixel / 127.5) - 1.0
           → range [-1.0, 1.0]

        PENTING: Normalisasi /127.5 - 1 HARUS SAMA dengan normalisasi saat training.
        Menggunakan /255.0 akan merusak akurasi karena distribusi input berbeda.
        """
        target_h, target_w = self.target_size[1], self.target_size[0]
        src_h, src_w = image_rgb.shape[:2]

        # Letterbox: hitung skala seragam agar gambar muat tanpa distorsi
        scale = min(target_w / src_w, target_h / src_h)
        new_w = int(src_w * scale)
        new_h = int(src_h * scale)

        # Pilih interpolation yang sesuai:
        # INTER_AREA terbaik untuk downscale (detail texture terjaga)
        # INTER_LINEAR terbaik untuk upscale
        if scale < 1.0:
            interpolation = cv2.INTER_AREA
        else:
            interpolation = cv2.INTER_LINEAR

        resized = cv2.resize(image_rgb, (new_w, new_h), interpolation=interpolation)

        # Buat canvas abu-abu netral (127, 127, 127) dan tempel gambar di tengah
        # Abu-abu netral → setelah normalisasi menjadi 0.0 (nilai tengah range)
        canvas = np.full((target_h, target_w, 3), 127, dtype=np.uint8)
        pad_top = (target_h - new_h) // 2
        pad_left = (target_w - new_w) // 2
        canvas[pad_top : pad_top + new_h, pad_left : pad_left + new_w] = resized

        # Normalisasi MobileNetV2: [-1.0, 1.0]
        batch = np.expand_dims(canvas.astype(np.float32), axis=0)
        return (batch / 127.5) - 1.0
