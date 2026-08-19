from __future__ import annotations

from dataclasses import dataclass
from datetime import date


@dataclass(frozen=True)
class PlantingRecommendationInput:
    latitude: float
    longitude: float
    rice_variety: str
    irrigation_type: str
    land_area_hectares: float
    preferred_start_date: date
