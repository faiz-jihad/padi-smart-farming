from __future__ import annotations

from datetime import date

from pydantic import BaseModel, Field


class PlantingRecommendationRequest(BaseModel):
    latitude: float = Field(ge=-90, le=90)
    longitude: float = Field(ge=-180, le=180)
    rice_variety: str = Field(min_length=1)
    irrigation_type: str = Field(min_length=1)
    land_area_hectares: float = Field(gt=0)
    preferred_start_date: date


class PlantingRecommendationResponse(BaseModel):
    suitability_score: int
    risk_level: str
    recommended_start_date: date
    recommended_end_date: date
    reasons: list[str]
    weather_source: str
