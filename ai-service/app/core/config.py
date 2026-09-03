from __future__ import annotations

import json
import os
from dataclasses import dataclass, field
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


def _env_json_mapping(name: str, default: dict[str, str]) -> dict[str, str]:
    raw_value = os.getenv(name)
    if not raw_value:
        return default
    parsed_value = json.loads(raw_value)
    return {str(key): str(value) for key, value in parsed_value.items()}


def _find_local_file(path_value: str) -> Path | None:
    path = Path(path_value)
    candidates = [
        path,
        Path.cwd() / path,
        Path.cwd() / "ai-service" / path,
        Path(__file__).resolve().parents[2] / path,
    ]
    for candidate in candidates:
        if candidate.exists():
            return candidate
    return None


def _load_json_file(path_value: str) -> dict:
    path = _find_local_file(path_value)
    if path is None:
        return {}
    try:
        return json.loads(path.read_text(encoding="utf-8"))
    except (OSError, json.JSONDecodeError):
        return {}


def _default_model_class_mapping() -> dict[str, str]:
    file_mapping = _load_json_file(_env("MODEL_CLASS_LABELS_PATH", "models/class_labels.json"))
    if file_mapping:
        return {str(key): str(value) for key, value in file_mapping.items()}

    return {
        "0": "bacterial_leaf_blight",
        "1": "bacterial_leaf_streak",
        "2": "bacterial_panicle_blight",
        "3": "blast",
        "4": "brown_spot",
        "5": "dead_heart",
        "6": "downy_mildew",
        "7": "hispa",
        "8": "healthy",
        "9": "tungro",
    }


def _model_metadata() -> dict:
    return _load_json_file(_env("MODEL_METADATA_PATH", "models/model_metadata.json"))


@dataclass(frozen=True)
class Settings:
    app_name: str = field(default_factory=lambda: _env("APP_NAME", "padi-ai-service"))
    app_env: str = field(default_factory=lambda: _env("APP_ENV", "development"))
    app_debug: bool = field(default_factory=lambda: _env_bool("APP_DEBUG", False))
    api_v1_prefix: str = field(default_factory=lambda: _env("API_V1_PREFIX", "/api/v1"))
    allowed_origins: list[str] = field(default_factory=lambda: _env_list("ALLOWED_ORIGINS", "http://localhost:3000"))

    model_path: Path = field(
        default_factory=lambda: Path(_env("MODEL_PATH", "models/YOLO11L-Rice-Disease-Detection.pt"))
    )
    model_version: str = field(
        default_factory=lambda: _env("MODEL_VERSION", str(_model_metadata().get("version", "1.0.0")))
    )
    model_reported_accuracy: float | None = field(
        default_factory=lambda: (
            float(_env("MODEL_REPORTED_ACCURACY", str(_model_metadata().get("accuracy"))))
            if _env("MODEL_REPORTED_ACCURACY", str(_model_metadata().get("accuracy"))) not in {"", "None"}
            else None
        )
    )
    model_confidence_high: float = field(default_factory=lambda: _env_float("MODEL_CONFIDENCE_HIGH", 0.85))
    model_confidence_medium: float = field(default_factory=lambda: _env_float("MODEL_CONFIDENCE_MEDIUM", 0.70))
    model_class_mapping: dict[str, str] = field(
        default_factory=lambda: _env_json_mapping(
            "MODEL_CLASS_MAPPING",
            _default_model_class_mapping(),
        )
    )

    max_image_size_mb: int = field(default_factory=lambda: _env_int("MAX_IMAGE_SIZE_MB", 5))
    min_blur_score: float = field(default_factory=lambda: _env_float("MIN_BLUR_SCORE", 100.0))
    min_brightness: float = field(default_factory=lambda: _env_float("MIN_BRIGHTNESS", 40.0))
    max_brightness: float = field(default_factory=lambda: _env_float("MAX_BRIGHTNESS", 220.0))
    min_leaf_ratio: float = field(default_factory=lambda: _env_float("MIN_LEAF_RATIO", 0.12))
    min_disease_confidence: float = field(default_factory=lambda: _env_float("MIN_DISEASE_CONFIDENCE", 0.35))

    llm_api_key: str = field(default_factory=lambda: _env("LLM_API_KEY", ""))
    llm_model: str = field(default_factory=lambda: _env("LLM_MODEL", ""))
    llm_base_url: str = field(default_factory=lambda: _env("LLM_BASE_URL", ""))
    llm_timeout_seconds: float = field(default_factory=lambda: _env_float("LLM_TIMEOUT_SECONDS", 15.0))

    weather_api_key: str = field(default_factory=lambda: _env("WEATHER_API_KEY", ""))
    weather_base_url: str = field(default_factory=lambda: _env("WEATHER_BASE_URL", ""))
    weather_timeout_seconds: float = field(default_factory=lambda: _env_float("WEATHER_TIMEOUT_SECONDS", 10.0))

    @property
    def max_image_size_bytes(self) -> int:
        return self.max_image_size_mb * 1024 * 1024


@lru_cache
def get_settings() -> Settings:
    """Mengambil konfigurasi aplikasi dari environment variable."""
    _load_local_env()
    return Settings()
