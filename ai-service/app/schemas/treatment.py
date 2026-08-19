from __future__ import annotations

from pydantic import BaseModel, Field


class TreatmentRecommendationRequest(BaseModel):
    disease_code: str
    confidence: float = Field(ge=0, le=1)
    plant_age_days: int | None = Field(default=None, ge=0)
    severity: str
    affected_area_percentage: float = Field(ge=0, le=100)
    weather_condition: str | None = None
    actions_already_taken: list[str] = Field(default_factory=list)


class TreatmentRecommendationResponse(BaseModel):
    condition_summary: str
    immediate_actions: list[str]
    prevention_steps: list[str]
    danger_signs: list[str]
    extension_officer_advice: str
    sources: list[str]
    disclaimer: str
