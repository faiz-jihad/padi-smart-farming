from __future__ import annotations

from datetime import date, timedelta

import httpx

from app.core.exceptions import ExternalServiceError, ExternalServiceTimeoutError


class WeatherClient:
    def __init__(self, base_url: str, api_key: str, timeout_seconds: float) -> None:
        self.base_url = base_url
        self.api_key = api_key
        self.timeout_seconds = timeout_seconds

    @property
    def is_configured(self) -> bool:
        return bool(self.base_url)

    def fetch_daily_forecast(self, latitude: float, longitude: float) -> list[dict]:
        """Mengambil forecast harian dari provider eksternal jika dikonfigurasi."""
        if not self.is_configured:
            return self._fallback_forecast()

        try:
            response = httpx.get(
                self.base_url,
                params={"latitude": latitude, "longitude": longitude, "apikey": self.api_key},
                timeout=self.timeout_seconds,
            )
            response.raise_for_status()
            payload = response.json()
            return list(payload.get("daily", []))
        except httpx.TimeoutException as exc:
            raise ExternalServiceTimeoutError("Weather API timeout.", code="WEATHER_TIMEOUT") from exc
        except httpx.HTTPError as exc:
            raise ExternalServiceError("Weather API gagal diakses.", code="WEATHER_REQUEST_FAILED") from exc

    def _fallback_forecast(self) -> list[dict]:
        today = date.today()
        return [
            {
                "date": (today + timedelta(days=day_index)).isoformat(),
                "rainfall_mm": 18.0,
                "temperature_c": 28.0,
                "humidity_percent": 78.0,
            }
            for day_index in range(7)
        ]
