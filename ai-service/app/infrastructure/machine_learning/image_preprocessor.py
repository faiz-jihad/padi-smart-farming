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

    def preprocess_for_model(self, image_rgb: np.ndarray) -> np.ndarray:
        resized_image = cv2.resize(image_rgb, self.target_size, interpolation=cv2.INTER_AREA)
        batch = np.expand_dims(resized_image.astype(np.float32), axis=0)
        return (batch / 127.5) - 1.0
