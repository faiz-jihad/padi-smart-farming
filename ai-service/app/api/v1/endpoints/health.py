from __future__ import annotations

from fastapi import APIRouter, Depends

from app.api.dependencies import ServiceContainer, get_container
from app.schemas.common import HealthResponse

router = APIRouter(tags=["health"])


@router.get("/health", response_model=HealthResponse)
def health_check(container: ServiceContainer = Depends(get_container)) -> HealthResponse:  # noqa: B008
    classifier = container.disease_classifier
    return HealthResponse(
        status="healthy",
        service=container.settings.app_name,
        model_loaded=classifier.is_loaded,
        model_version=classifier.model_version,
        model_error=classifier.load_error,
    )
