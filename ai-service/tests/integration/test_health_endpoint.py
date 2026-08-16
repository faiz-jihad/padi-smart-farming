from fastapi.testclient import TestClient

from app.core.config import get_settings
from app.main import create_app


def test_health_endpoint_returns_model_status(monkeypatch):
    monkeypatch.setenv("MODEL_PATH", "missing-model.h5")
    get_settings.cache_clear()
    app = create_app()

    with TestClient(app) as client:
        response = client.get("/api/v1/health")

    assert response.status_code == 200
    assert response.json()["service"] == "padi-ai-service"
    assert response.json()["model_loaded"] is False
