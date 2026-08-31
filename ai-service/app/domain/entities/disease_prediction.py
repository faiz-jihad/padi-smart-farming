from __future__ import annotations

from dataclasses import dataclass


@dataclass(frozen=True)
class ImageQuality:
    is_acceptable: bool
    blur_score: float
    brightness_score: float
    warnings: list[str]


@dataclass(frozen=True)
class PredictionCandidate:
    disease_code: str
    disease_name: str
    confidence: float


@dataclass(frozen=True)
class DiseasePrediction:
    disease_code: str
    disease_name: str
    confidence: float
    confidence_level: str
    image_quality: ImageQuality
    needs_expert_review: bool
    model_version: str
    processing_time_ms: int
    top_predictions: list[PredictionCandidate]
    prediction_margin: float
    model_accuracy: float | None = None
