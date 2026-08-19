from __future__ import annotations

from fastapi import APIRouter, Depends

from app.api.dependencies import get_recommend_planting_time_use_case
from app.application.dto.planting_recommendation_dto import PlantingRecommendationInput
from app.application.use_cases.recommend_planting_time import RecommendPlantingTimeUseCase
from app.core.logging import request_id_context
from app.schemas.common import MetaResponse, SuccessResponse
from app.schemas.planting import PlantingRecommendationRequest, PlantingRecommendationResponse

router = APIRouter(prefix="/planting", tags=["planting-recommendation"])


@router.post("/recommend", response_model=SuccessResponse)
def recommend_planting_time(
    request_body: PlantingRecommendationRequest,
    use_case: RecommendPlantingTimeUseCase = Depends(get_recommend_planting_time_use_case),  # noqa: B008
) -> SuccessResponse:
    recommendation = use_case.execute(PlantingRecommendationInput(**request_body.model_dump()))
    return SuccessResponse(
        data=PlantingRecommendationResponse(**recommendation.__dict__),
        meta=MetaResponse(request_id=request_id_context.get()),
    )
