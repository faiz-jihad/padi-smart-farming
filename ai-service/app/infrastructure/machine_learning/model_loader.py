from __future__ import annotations

import logging
from pathlib import Path
from typing import Any

from app.core.exceptions import ModelUnavailableError

logger = logging.getLogger(__name__)


class KerasModelLoader:
    def load(self, model_path: Path) -> Any:
        """Memuat model Keras dari path konfigurasi dengan automatic path resolution."""
        resolved_path = model_path
        if not resolved_path.exists():
            candidates = [
                Path.cwd() / "models" / "model_penyakit_padi_v2_finetuned.h5",
                Path.cwd() / "ai-service" / "models" / "model_penyakit_padi_v2_finetuned.h5",
                Path(__file__).resolve().parents[3] / "models" / "model_penyakit_padi_v2_finetuned.h5",
                Path(__file__).resolve().parents[2] / "models" / "model_penyakit_padi_v2_finetuned.h5",
            ]
            for candidate in candidates:
                if candidate.exists():
                    resolved_path = candidate
                    logger.info("event=model_path_resolved path=%s", resolved_path)
                    break

        if not resolved_path.exists():
            raise ModelUnavailableError(f"Model tidak ditemukan di path: {model_path}", code="MODEL_NOT_FOUND")
        try:
            from tensorflow import keras

            return keras.models.load_model(resolved_path, compile=False)
        except Exception as exc:
            logger.exception("event=model_load_failed path=%s", resolved_path)
            raise ModelUnavailableError("Model gagal dimuat.", code="MODEL_LOAD_FAILED") from exc
