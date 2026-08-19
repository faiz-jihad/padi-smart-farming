from __future__ import annotations

import logging
from pathlib import Path

import numpy as np

from app.core.exceptions import ModelUnavailableError
from app.infrastructure.machine_learning.image_preprocessor import ImagePreprocessor
from app.infrastructure.machine_learning.label_mapper import LabelMapper
from app.infrastructure.machine_learning.model_loader import KerasModelLoader

logger = logging.getLogger(__name__)


class DiseaseClassifier:
    def __init__(
        self,
        model_path: Path,
        model_version: str,
        image_preprocessor: ImagePreprocessor,
        label_mapper: LabelMapper,
        model_loader: KerasModelLoader | None = None,
    ) -> None:
        self._model_path = model_path
        self._model_version = model_version
        self._image_preprocessor = image_preprocessor
        self._label_mapper = label_mapper
        self._model_loader = model_loader or KerasModelLoader()
        self._model = None
        self._load_error: str | None = None

    @property
    def is_loaded(self) -> bool:
        return self._model is not None

    @property
    def model_version(self) -> str:
        return self._model_version

    @property
    def load_error(self) -> str | None:
        return self._load_error

    def load(self) -> None:
        """Memuat model satu kali saat startup aplikasi."""
        try:
            self._model = self._model_loader.load(self._model_path)
            self._load_error = None
            logger.info("event=model_loaded version=%s", self._model_version)
        except ModelUnavailableError as exc:
            self._load_error = exc.code
            logger.error("event=model_unavailable code=%s", exc.code)

    def predict(self, image_rgb) -> tuple[str, str, float]:
        if self._model is None:
            raise ModelUnavailableError("Model belum tersedia.", code=self._load_error or "MODEL_NOT_LOADED")

        model_input = self._image_preprocessor.preprocess_for_model(image_rgb)
        probabilities = self._model.predict(model_input, verbose=0)
        flattened_probabilities = np.asarray(probabilities).reshape(-1)
        class_index = int(flattened_probabilities.argmax())
        confidence = float(flattened_probabilities[class_index])
        disease_code, disease_name = self._label_mapper.map_index(class_index)
        return disease_code, disease_name, confidence
