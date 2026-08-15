from __future__ import annotations

from datetime import date

from app.domain.entities.planting_recommendation import WeatherForecastDay
from app.infrastructure.weather.weather_client import WeatherClient


class WeatherRepositoryImpl:
    def __init__(self, weather_client: WeatherClient) -> None:
        self.weather_client = weather_client

    def get_forecast(self, latitude: float, longitude: float) -> tuple[list[WeatherForecastDay], str]:
        forecast_payload = self.weather_client.fetch_daily_forecast(latitude, longitude)
        source = "external-weather-api" if self.weather_client.is_configured else "local-development-fallback"
        return [self._parse_forecast_day(item) for item in forecast_payload], source

    def _parse_forecast_day(self, item: dict) -> WeatherForecastDay:
        return WeatherForecastDay(
            forecast_date=date.fromisoformat(str(item["date"])),
            rainfall_mm=float(item["rainfall_mm"]),
            temperature_c=float(item["temperature_c"]),
            humidity_percent=float(item["humidity_percent"]),
        )
