from __future__ import annotations

import logging
from pathlib import Path
from typing import Any

import numpy as np

from app.core.exceptions import ModelUnavailableError
from app.infrastructure.machine_learning.label_mapper import LabelMapper

logger = logging.getLogger(__name__)


class YoloDiseaseDetector:
    """Ultralytics YOLO detector/classifier untuk model penyakit padi `.pt`."""

    def __init__(
        self,
        model_path: Path,
        model_version: str,
        label_mapper: LabelMapper,
        confidence_floor_for_no_detection: float = 0.50,
    ) -> None:
        self._model_path = model_path
        self._model_version = model_version
        self._label_mapper = label_mapper
        self._confidence_floor_for_no_detection = confidence_floor_for_no_detection
        self._model: Any | None = None
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
        resolved_path = self._resolve_model_path(self._model_path)
        if resolved_path is None:
            self._load_error = "MODEL_NOT_FOUND"
            logger.error("event=yolo_model_not_found path=%s", self._model_path)
            return

        try:
            from ultralytics import YOLO

            self._model = YOLO(str(resolved_path))
            self._load_error = None
            logger.info("event=yolo_model_loaded version=%s path=%s", self._model_version, resolved_path)
        except ImportError:
            self._load_error = "YOLO_RUNTIME_MISSING"
            logger.exception("event=yolo_runtime_missing")
            return
        except Exception:
            self._load_error = "MODEL_LOAD_FAILED"
            logger.exception("event=yolo_model_load_failed path=%s", resolved_path)
            return

    def predict(self, image_rgb) -> tuple[str, str, float]:
        disease_code, disease_name, confidence, _, _ = self.predict_top(image_rgb, top_k=1)
        return disease_code, disease_name, confidence

    def predict_top(self, image_rgb, top_k: int = 3) -> tuple[str, str, float, list[dict[str, float | str]], float]:
        if self._model is None:
            raise ModelUnavailableError("Model YOLO belum tersedia.", code=self._load_error or "MODEL_NOT_LOADED")

        results = self._model.predict(source=image_rgb, verbose=False)
        if not results:
            return self._healthy_no_detection_result()

        first_result = results[0]
        candidates = self._classification_candidates(first_result) or self._detection_candidates(first_result)
        if not candidates:
            return self._healthy_no_detection_result()

        candidates = sorted(candidates, key=lambda item: float(item["confidence"]), reverse=True)
        top_predictions = candidates[: max(1, top_k)]
        confidence = float(top_predictions[0]["confidence"])
        second_confidence = float(top_predictions[1]["confidence"]) if len(top_predictions) > 1 else 0.0

        return (
            str(top_predictions[0]["disease_code"]),
            str(top_predictions[0]["disease_name"]),
            confidence,
            top_predictions,
            max(0.0, confidence - second_confidence),
        )

    def _classification_candidates(self, result: Any) -> list[dict[str, float | str]]:
        probs = getattr(result, "probs", None)
        if probs is None:
            return []

        raw_probs = getattr(probs, "data", None)
        if raw_probs is None:
            raw_probs = probs

        probabilities = self._to_numpy(raw_probs).reshape(-1)
        if probabilities.size == 0:
            return []

        candidates: list[dict[str, float | str]] = []
        for index in np.argsort(probabilities)[::-1]:
            candidate = self._candidate_from_index(result, int(index), float(probabilities[int(index)]))
            if candidate is not None:
                candidates.append(candidate)
        return candidates

    def _detection_candidates(self, result: Any) -> list[dict[str, float | str]]:
        boxes = getattr(result, "boxes", None)
        if boxes is None:
            return []

        classes = self._to_numpy(getattr(boxes, "cls", [])).reshape(-1)
        confidences = self._to_numpy(getattr(boxes, "conf", [])).reshape(-1)
        if classes.size == 0 or confidences.size == 0:
            return []

        best_by_code: dict[str, dict[str, float | str]] = {}
        for class_index, confidence in zip(classes.astype(int), confidences.astype(float), strict=False):
            candidate = self._candidate_from_index(result, int(class_index), float(confidence))
            if candidate is None:
                continue
            disease_code = str(candidate["disease_code"])
            current_best = best_by_code.get(disease_code)
            if current_best is None or float(candidate["confidence"]) > float(current_best["confidence"]):
                best_by_code[disease_code] = candidate

        return list(best_by_code.values())

    def _candidate_from_index(self, result: Any, class_index: int, confidence: float) -> dict[str, float | str] | None:
        names = getattr(result, "names", None) or getattr(self._model, "names", None) or {}
        raw_label = self._label_from_names(names, class_index)
        if raw_label:
            disease_code = self._label_mapper.normalize_label(raw_label)
            disease_name = self._label_mapper.display_name_for_code(disease_code)
        else:
            disease_code, disease_name = self._label_mapper.map_index(class_index)

        if disease_code == "unknown":
            return None

        return {
            "disease_code": disease_code,
            "disease_name": disease_name,
            "confidence": round(min(max(float(confidence), 0.0), 1.0), 4),
        }

    def _healthy_no_detection_result(self) -> tuple[str, str, float, list[dict[str, float | str]], float]:
        disease_name = self._label_mapper.display_name_for_code("healthy")
        confidence = round(self._confidence_floor_for_no_detection, 4)
        return (
            "healthy",
            disease_name,
            confidence,
            [{"disease_code": "healthy", "disease_name": disease_name, "confidence": confidence}],
            0.0,
        )

    def _label_from_names(self, names: Any, class_index: int) -> str:
        if isinstance(names, dict):
            return str(names.get(class_index) or names.get(str(class_index)) or "").strip()
        if isinstance(names, (list, tuple)) and 0 <= class_index < len(names):
            return str(names[class_index]).strip()
        return ""

    def _to_numpy(self, value: Any) -> np.ndarray:
        if hasattr(value, "detach"):
            value = value.detach()
        if hasattr(value, "cpu"):
            value = value.cpu()
        if hasattr(value, "numpy"):
            return np.asarray(value.numpy(), dtype=np.float32)
        return np.asarray(value, dtype=np.float32)

    def _resolve_model_path(self, model_path: Path) -> Path | None:
        candidates = [
            model_path,
            Path.cwd() / model_path,
            Path.cwd() / "ai-service" / model_path,
            Path(__file__).resolve().parents[3] / model_path,
            Path(__file__).resolve().parents[2] / model_path,
        ]
        if model_path.name:
            candidates.extend(
                [
                    Path.cwd() / "models" / model_path.name,
                    Path.cwd() / "ai-service" / "models" / model_path.name,
                    Path(__file__).resolve().parents[3] / "models" / model_path.name,
                    Path(__file__).resolve().parents[2] / "models" / model_path.name,
                ]
            )

        for candidate in candidates:
            if candidate.exists():
                return candidate
        return None
