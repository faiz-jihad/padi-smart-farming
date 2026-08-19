from __future__ import annotations

from pydantic import BaseModel


class ImageQualityResponse(BaseModel):
    is_acceptable: bool
    blur_score: float
    brightness_score: float
    warnings: list[str]


class DiseaseDetectionResponse(BaseModel):
    disease_code: str
    disease_name: str
    confidence: float
    confidence_level: str
    image_quality: ImageQualityResponse
    needs_expert_review: bool
    model_version: str
    processing_time_ms: int
