from __future__ import annotations

from dataclasses import dataclass
from datetime import date


@dataclass(frozen=True)
class WeatherForecastDay:
    forecast_date: date
    rainfall_mm: float
    temperature_c: float
    humidity_percent: float


@dataclass(frozen=True)
class PlantingRecommendation:
    suitability_score: int
    risk_level: str
    recommended_start_date: date
    recommended_end_date: date
    reasons: list[str]
    weather_source: str
