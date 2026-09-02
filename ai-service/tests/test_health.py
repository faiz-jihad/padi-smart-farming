"""
Tests untuk health endpoints.
"""
from __future__ import annotations


def test_health_simple(client):
    """Health endpoint mengembalikan status ok."""
    response = client.get("/health")
    assert response.status_code == 200
    data = response.json()
    assert data["status"] == "ok"


def test_health_detail_model_loaded(client):
    """Detail health menunjukkan model_loaded = true."""
    response = client.get("/api/v1/ai/health")
    assert response.status_code == 200
    data = response.json()
    assert data["model_loaded"] is True
    assert data["status"] in ("healthy", "degraded")
    assert "device" in data
    assert "model_version" in data
    assert "model_name" in data
