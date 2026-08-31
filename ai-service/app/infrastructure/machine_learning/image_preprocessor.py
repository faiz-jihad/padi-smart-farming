from __future__ import annotations

import cv2
import numpy as np

from app.core.exceptions import ImageValidationError


class ImagePreprocessor:
    def __init__(self, target_size: tuple[int, int] = (224, 224)) -> None:
        self.target_size = target_size

    def decode(self, content: bytes) -> np.ndarray:
        """Decode byte gambar menjadi array RGB."""
        image_array = np.frombuffer(content, dtype=np.uint8)
        decoded_image = cv2.imdecode(image_array, cv2.IMREAD_COLOR)
        if decoded_image is None:
            raise ImageValidationError("Gambar tidak dapat dibaca.", code="INVALID_IMAGE")
        return cv2.cvtColor(decoded_image, cv2.COLOR_BGR2RGB)

    def measure_quality(self, image_rgb: np.ndarray) -> tuple[float, float]:
        """Mengukur blur dan brightness tanpa menyimpan gambar."""
        grayscale_image = cv2.cvtColor(image_rgb, cv2.COLOR_RGB2GRAY)
        blur_score = float(cv2.Laplacian(grayscale_image, cv2.CV_64F).var())
        brightness_score = float(grayscale_image.mean())
        return blur_score, brightness_score

    def analyze_leaf_features(self, image_rgb: np.ndarray) -> dict[str, float]:
        """Menganalisis karakteristik visual untuk memastikan objek adalah daun tanaman padi."""
        total_pixels = max(1, image_rgb.shape[0] * image_rgb.shape[1])
        hsv = cv2.cvtColor(image_rgb, cv2.COLOR_RGB2HSV)

        # 1. Spektrum warna daun (daun sehat hijau dan daun berpenyakit kuning/cokelat/hawar)
        green_mask = cv2.inRange(hsv, np.array([25, 25, 25], dtype=np.uint8), np.array([95, 255, 255], dtype=np.uint8))
        yellow_brown_mask = cv2.inRange(hsv, np.array([8, 30, 30], dtype=np.uint8), np.array([25, 255, 255], dtype=np.uint8))
        straw_mask = cv2.inRange(hsv, np.array([15, 20, 35], dtype=np.uint8), np.array([35, 200, 220], dtype=np.uint8))
        leaf_mask = cv2.bitwise_or(green_mask, cv2.bitwise_or(yellow_brown_mask, straw_mask))
        leaf_ratio = float(cv2.countNonZero(leaf_mask) / total_pixels)

        # 2. Spektrum warna kulit manusia (wajah, selfie, tangan)
        ycrcb = cv2.cvtColor(image_rgb, cv2.COLOR_RGB2YCrCb)
        skin_mask = cv2.inRange(ycrcb, np.array([0, 133, 77], dtype=np.uint8), np.array([255, 173, 127], dtype=np.uint8))
        skin_ratio = float(cv2.countNonZero(skin_mask) / total_pixels)

        # 3. Spektrum warna sintetis buatan (biru/magenta dominan)
        unnatural_mask = cv2.inRange(hsv, np.array([96, 50, 50], dtype=np.uint8), np.array([170, 255, 255], dtype=np.uint8))
        unnatural_ratio = float(cv2.countNonZero(unnatural_mask) / total_pixels)

        # 4. Rata-rata saturasi (mendeteksi monokrom/kertas/dokumen)
        mean_saturation = float(np.mean(hsv[:, :, 1]))

        return {
            "leaf_ratio": round(leaf_ratio, 4),
            "skin_ratio": round(skin_ratio, 4),
            "unnatural_ratio": round(unnatural_ratio, 4),
            "mean_saturation": round(mean_saturation, 2),
        }

    def preprocess_for_model(self, image_rgb: np.ndarray) -> np.ndarray:
        resized_image = cv2.resize(image_rgb, self.target_size, interpolation=cv2.INTER_AREA)
        batch = np.expand_dims(resized_image.astype(np.float32), axis=0)
        return (batch / 127.5) - 1.0

