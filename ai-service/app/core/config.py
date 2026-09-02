from __future__ import annotations

import os
from functools import lru_cache
from pathlib import Path


def _load_local_env() -> None:
    env_path = Path.cwd() / ".env"
    if not env_path.exists():
        return
    for raw_line in env_path.read_text(encoding="utf-8").splitlines():
        line = raw_line.strip()
        if not line or line.startswith("#") or "=" not in line:
            continue
        key, value = line.split("=", 1)
        os.environ.setdefault(key.strip(), value.strip().strip('"').strip("'"))


def _env(name: str, default: str) -> str:
    return os.getenv(name, default)


def _env_bool(name: str, default: bool) -> bool:
    value = os.getenv(name)
    if value is None:
        return default
    return value.strip().lower() in {"1", "true", "yes", "on"}


def _env_float(name: str, default: float) -> float:
    value = os.getenv(name)
    return default if value is None or value == "" else float(value)


def _env_int(name: str, default: int) -> int:
    value = os.getenv(name)
    return default if value is None or value == "" else int(value)


def _env_list(name: str, default: str) -> list[str]:
    value = _env(name, default)
    return [item.strip() for item in value.split(",") if item.strip()]


class Settings:
    def __init__(self) -> None:
        # App
        self.app_name: str = _env("APP_NAME", "padi-ai-service")
        self.app_env: str = _env("APP_ENV", "development")
        self.app_debug: bool = _env_bool("APP_DEBUG", False)
        self.api_v1_prefix: str = _env("API_V1_PREFIX", "/api/v1")
        self.allowed_origins: list[str] = _env_list(
            "CORS_ORIGINS",
            "http://localhost:3000,http://127.0.0.1:8000,http://localhost:8000",
        )

        # Model
        self.model_name: str = _env("PADI_MODEL_NAME", "paddy_doctor")
        self.model_version: str = _env("PADI_MODEL_VERSION", "v3")
        self.model_path: Path = self._resolve_model_path(
            _env("MODEL_PATH", f"models/padi.pt")
        )
        self.model_imgsz: int = _env_int("MODEL_IMGSZ", 384)
        self.model_full_name: str = f"{self.model_name}_{self.model_version}"

        # Expected model properties
        self.expected_task: str = "classify"
        self.expected_num_classes: int = 10
        self.expected_classes: list[str] = [
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

        # GPU/CPU concurrency
        self.max_gpu_concurrency: int = _env_int("PADI_MAX_GPU_CONCURRENCY", 1)

        # Image upload
        self.max_image_size_mb: int = _env_int("MAX_IMAGE_SIZE_MB", 10)
        self.min_image_width: int = _env_int("QUALITY_MIN_WIDTH", 200)
        self.min_image_height: int = _env_int("QUALITY_MIN_HEIGHT", 200)

        # Image quality thresholds
        self.quality_blur_threshold: float = _env_float("QUALITY_BLUR_THRESHOLD", 50.0)
        self.quality_brightness_min: float = _env_float("QUALITY_BRIGHTNESS_MIN", 45.0)
        self.quality_brightness_max: float = _env_float("QUALITY_BRIGHTNESS_MAX", 225.0)

        # Decision thresholds
        self.high_confidence_threshold: float = _env_float("HIGH_CONFIDENCE_THRESHOLD", 0.85)
        self.review_confidence_threshold: float = _env_float("REVIEW_CONFIDENCE_THRESHOLD", 0.60)
        self.min_margin_threshold: float = _env_float("MIN_MARGIN_THRESHOLD", 0.20)

        # Multi-view (disabled by default)
        self.enable_multiview: bool = _env_bool("PADI_ENABLE_MULTIVIEW", False)

        # Catalog
        self.disease_catalog_path: Path = self._resolve_data_path(
            _env("DISEASE_CATALOG_PATH", "data/disease_catalog.json")
        )

    @property
    def max_image_size_bytes(self) -> int:
        return self.max_image_size_mb * 1024 * 1024

    def _resolve_model_path(self, path_str: str) -> Path:
        path = Path(path_str)
        candidates = [
            path,
            Path.cwd() / path,
            Path.cwd() / "ai-service" / path,
            Path(__file__).resolve().parents[2] / path,
        ]
        for c in candidates:
            if c.exists():
                return c
        # Return best-guess even if not found yet (validated at startup)
        return Path.cwd() / path

    def _resolve_data_path(self, path_str: str) -> Path:
        path = Path(path_str)
        candidates = [
            path,
            Path.cwd() / path,
            Path.cwd() / "ai-service" / path,
            Path(__file__).resolve().parents[2] / path,
        ]
        for c in candidates:
            if c.exists():
                return c
        return Path.cwd() / path


@lru_cache
def get_settings() -> Settings:
    _load_local_env()
    return Settings()
