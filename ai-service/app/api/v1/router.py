from __future__ import annotations

from fastapi import APIRouter

from app.api.v1.endpoints import (
    disease_detection,
    disease_learning,
    health,
    planting_recommendation,
    treatment_recommendation,
)

api_router = APIRouter()
api_router.include_router(health.router)
api_router.include_router(disease_detection.router)
api_router.include_router(disease_learning.router)
api_router.include_router(treatment_recommendation.router)
api_router.include_router(planting_recommendation.router)

