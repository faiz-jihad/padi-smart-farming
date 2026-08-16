from __future__ import annotations

from typing import Protocol

from app.domain.entities.planting_recommendation import WeatherForecastDay


class WeatherRepository(Protocol):
    def get_forecast(self, latitude: float, longitude: float) -> tuple[list[WeatherForecastDay], str]:
        """Mengambil prakiraan cuaca untuk koordinat tertentu."""
