from __future__ import annotations

import asyncio
import logging
import time
import uuid
from dataclasses import dataclass, field
from pathlib import Path
from typing import Any

import numpy as np

logger = logging.getLogger(__name__)

EXPECTED_CLASSES: list[str] = [
    "bacterial_leaf_blight",
    "bacterial_leaf_streak",
    "bacterial_panicle_blight",
    "blast",
    "brown_spot",
    "dead_heart",
    "downy_mildew",
    "hispa",
    "normal",
    "tungro",
]


@dataclass
class PredictionCandidate:
    rank: int
    class_id: int
    class_name: str
    confidence: float


@dataclass
class ClassifierResult:
    request_id: str
    class_id: int
    class_name: str
    confidence: float
    confidence_percent: float
    margin: float
    top_predictions: list[PredictionCandidate]
    preprocessing_ms: float
    inference_ms: float
    total_latency_ms: float
    model_name: str
    model_version: str
    model_task: str
    model_imgsz: int
    device: str


class PADIClassifier:
    """
    Singleton AI classifier untuk penyakit padi berbasis YOLO classify.

    Bertanggung jawab untuk:
    - load dan validasi model saat startup
    - warmup
    - inference dengan torch.inference_mode()
    - top-k extraction
    - latency measurement
    - concurrency control via asyncio.Semaphore
    """

    def __init__(
        self,
        model_path: Path,
        model_name: str,
        model_version: str,
        model_imgsz: int = 384,
        max_concurrency: int = 1,
    ) -> None:
        self._model_path = model_path
        self._model_name = model_name
        self._model_version = model_version
        self._model_imgsz = model_imgsz
        self._model: Any = None
        self._device: str = "cpu"
        self._model_names: dict[int, str] = {}
        self._semaphore: asyncio.Semaphore = asyncio.Semaphore(max_concurrency)
        self._loaded: bool = False
        self._load_error: str | None = None

    @property
    def is_loaded(self) -> bool:
        return self._loaded

    @property
    def device(self) -> str:
        return self._device

    @property
    def model_names(self) -> dict[int, str]:
        return self._model_names

    @property
    def model_name(self) -> str:
        return self._model_name

    @property
    def model_version(self) -> str:
        return self._model_version

    @property
    def model_imgsz(self) -> int:
        return self._model_imgsz

    def load_and_validate(self) -> None:
        """
        Load model, validasi task==classify, validasi 10 classes, lakukan warmup.
        Dipanggil satu kali saat FastAPI startup.
        Gagal keras jika validasi tidak lolos.
        """
        from app.core.exceptions import ModelUnavailableError

        resolved = self._resolve_path()
        if resolved is None:
            raise ModelUnavailableError(
                f"Model tidak ditemukan: {self._model_path}. "
                "Pastikan models/padi.pt ada di direktori ai-service.",
                code="MODEL_NOT_FOUND",
            )

        logger.info("event=model_loading path=%s", resolved)

        try:
            import torch
            from ultralytics import YOLO

            self._device = "0" if torch.cuda.is_available() else "cpu"
            device_label = f"cuda:{self._device}" if self._device == "0" else "cpu"
            logger.info("event=device_selected device=%s", device_label)

            model = YOLO(str(resolved))

        except ImportError as exc:
            raise ModelUnavailableError(
                f"Ultralytics atau PyTorch tidak terinstall: {exc}",
                code="RUNTIME_MISSING",
            ) from exc
        except Exception as exc:
            raise ModelUnavailableError(
                f"Gagal memuat model: {exc}",
                code="MODEL_LOAD_FAILED",
            ) from exc

        # Validasi task
        task = getattr(model, "task", None)
        if task != "classify":
            raise ModelUnavailableError(
                f"Model task harus 'classify', ditemukan: '{task}'. "
                "Jangan gunakan model detection/segmentation untuk diagnosa penyakit padi.",
                code="WRONG_MODEL_TASK",
            )
        logger.info("event=model_task_validated task=%s", task)

        # Validasi classes
        names: dict = getattr(model, "names", {})
        if not names:
            raise ModelUnavailableError(
                "model.names kosong — tidak dapat membaca label kelas.",
                code="MODEL_NAMES_EMPTY",
            )

        num_classes = len(names)
        if num_classes != 10:
            raise ModelUnavailableError(
                f"Model harus memiliki tepat 10 kelas, ditemukan: {num_classes}. "
                f"Kelas ditemukan: {list(names.values())}",
                code="WRONG_NUM_CLASSES",
            )

        # Validasi nama kelas
        actual_classes = sorted(names.values())
        expected_sorted = sorted(EXPECTED_CLASSES)
        if actual_classes != expected_sorted:
            raise ModelUnavailableError(
                f"Nama kelas model tidak sesuai.\n"
                f"Expected: {expected_sorted}\n"
                f"Actual:   {actual_classes}",
                code="WRONG_CLASS_NAMES",
            )

        self._model = model
        self._model_names = {int(k): str(v) for k, v in names.items()}
        self._loaded = True
        logger.info(
            "event=model_validated classes=%s device=%s",
            list(self._model_names.values()),
            self._device,
        )

        # Warmup
        self._warmup()

    def _warmup(self) -> None:
        """Jalankan dummy inference untuk memastikan request pertama tidak lambat."""
        import numpy as np

        try:
            dummy = np.zeros((self._model_imgsz, self._model_imgsz, 3), dtype=np.uint8)
            t0 = time.perf_counter()
            self._run_inference(dummy)
            elapsed_ms = (time.perf_counter() - t0) * 1000
            logger.info("event=model_warmup_done latency_ms=%.1f", elapsed_ms)
        except Exception as exc:
            logger.warning("event=warmup_failed error=%s", exc)

    def _run_inference(self, image_rgb: np.ndarray) -> Any:
        """Internal synchronous inference. Harus dipanggil dalam semaphore context."""
        import torch

        with torch.inference_mode():
            results = self._model.predict(
                source=image_rgb,
                imgsz=self._model_imgsz,
                device=self._device,
                batch=1,
                verbose=False,
                save=False,
            )
        return results

    async def classify(self, image_rgb: np.ndarray) -> ClassifierResult:
        """
        Public async classify method.
        Menggunakan semaphore untuk GPU concurrency control.
        """
        from app.core.exceptions import InferenceFailedError, ModelUnavailableError

        if not self._loaded or self._model is None:
            raise ModelUnavailableError(
                "Model belum siap. Coba beberapa saat lagi.",
                code="MODEL_NOT_LOADED",
            )

        request_id = str(uuid.uuid4())
        t_start = time.perf_counter()

        try:
            async with self._semaphore:
                t_infer_start = time.perf_counter()
                loop = asyncio.get_event_loop()
                results = await loop.run_in_executor(None, self._run_inference, image_rgb)
                inference_ms = (time.perf_counter() - t_infer_start) * 1000
        except Exception as exc:
            logger.error("event=inference_failed request_id=%s error=%s", request_id, exc)
            raise InferenceFailedError(f"Inference gagal: {exc}") from exc

        total_ms = (time.perf_counter() - t_start) * 1000
        preprocessing_ms = total_ms - inference_ms

        if not results:
            raise InferenceFailedError("Model tidak menghasilkan output.")

        result = results[0]
        probs = getattr(result, "probs", None)
        if probs is None:
            raise InferenceFailedError(
                "result.probs kosong — pastikan model adalah YOLO classify, bukan detect/segment."
            )

        # Ambil probabilitas
        prob_data = getattr(probs, "data", None)
        if prob_data is None:
            raise InferenceFailedError("probs.data tidak tersedia.")

        import torch
        prob_tensor = prob_data.detach().cpu()
        prob_numpy = prob_tensor.numpy().reshape(-1).astype(float)

        if len(prob_numpy) == 0:
            raise InferenceFailedError("Array probabilitas kosong.")

        # Sort descending
        sorted_indices = np.argsort(prob_numpy)[::-1]
        top_k = min(3, len(sorted_indices))

        top_predictions: list[PredictionCandidate] = []
        for rank, idx in enumerate(sorted_indices[:top_k], start=1):
            class_id = int(idx)
            class_name = self._model_names.get(class_id, f"class_{class_id}")
            conf = float(np.clip(prob_numpy[idx], 0.0, 1.0))
            top_predictions.append(
                PredictionCandidate(
                    rank=rank,
                    class_id=class_id,
                    class_name=class_name,
                    confidence=round(conf, 6),
                )
            )

        top1 = top_predictions[0]
        top2_conf = top_predictions[1].confidence if len(top_predictions) > 1 else 0.0
        margin = float(np.clip(top1.confidence - top2_conf, 0.0, 1.0))

        device_label = f"cuda:{self._device}" if self._device == "0" else "cpu"

        return ClassifierResult(
            request_id=request_id,
            class_id=top1.class_id,
            class_name=top1.class_name,
            confidence=round(top1.confidence, 6),
            confidence_percent=round(top1.confidence * 100, 2),
            margin=round(margin, 6),
            top_predictions=top_predictions,
            preprocessing_ms=round(preprocessing_ms, 2),
            inference_ms=round(inference_ms, 2),
            total_latency_ms=round(total_ms, 2),
            model_name=self._model_name,
            model_version=self._model_version,
            model_task="classify",
            model_imgsz=self._model_imgsz,
            device=device_label,
        )

    def get_model_info(self) -> dict:
        """Return model metadata untuk endpoint /model/info."""
        device_label = f"cuda:{self._device}" if self._device == "0" else "cpu"
        return {
            "name": self._model_name,
            "version": self._model_version,
            "full_name": f"{self._model_name}_{self._model_version}",
            "task": "classify",
            "classes": list(self._model_names.values()),
            "num_classes": len(self._model_names),
            "imgsz": self._model_imgsz,
            "device": device_label,
            "loaded": self._loaded,
        }

    def _resolve_path(self) -> Path | None:
        candidates = [
            self._model_path,
            Path.cwd() / self._model_path,
            Path.cwd() / "ai-service" / self._model_path,
            Path(__file__).resolve().parents[3] / self._model_path,
            Path(__file__).resolve().parents[2] / self._model_path,
        ]
        name = self._model_path.name
        if name:
            candidates += [
                Path.cwd() / "models" / name,
                Path.cwd() / "ai-service" / "models" / name,
                Path(__file__).resolve().parents[3] / "models" / name,
                Path(__file__).resolve().parents[2] / "models" / name,
            ]
        for c in candidates:
            if c.exists():
                return c
        return None
