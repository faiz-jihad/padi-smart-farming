from __future__ import annotations

import logging
from pathlib import Path
from typing import Any

from app.core.exceptions import ModelUnavailableError

logger = logging.getLogger(__name__)


class KerasModelLoader:
    def load(self, model_path: Path) -> Any:
        """Memuat model Keras dari path konfigurasi."""
        if not model_path.exists():
            raise ModelUnavailableError(f"Model tidak ditemukan di path: {model_path}", code="MODEL_NOT_FOUND")
        try:
            from tensorflow import keras

            return keras.models.load_model(model_path, compile=False)
        except Exception as exc:
            logger.exception("event=model_load_failed path=%s", model_path)
            raise ModelUnavailableError("Model gagal dimuat.", code="MODEL_LOAD_FAILED") from exc
