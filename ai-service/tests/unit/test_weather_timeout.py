import httpx
import pytest

from app.core.exceptions import ExternalServiceTimeoutError
from app.infrastructure.weather.weather_client import WeatherClient


def test_weather_client_timeout(monkeypatch):
    def raise_timeout(*args, **kwargs):
        raise httpx.TimeoutException("timeout")

    monkeypatch.setattr(httpx, "get", raise_timeout)
    client = WeatherClient(base_url="https://weather.example.test", api_key="key", timeout_seconds=0.01)

    with pytest.raises(ExternalServiceTimeoutError) as error_info:
        client.fetch_daily_forecast(-6.3, 108.3)

    assert error_info.value.code == "WEATHER_TIMEOUT"
