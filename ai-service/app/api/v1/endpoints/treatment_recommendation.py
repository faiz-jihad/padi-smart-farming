from __future__ import annotations

from fastapi import APIRouter, Depends

from app.api.dependencies import get_generate_treatment_use_case
from app.application.use_cases.generate_treatment import GenerateTreatmentUseCase
from app.core.logging import request_id_context
from app.schemas.common import MetaResponse, SuccessResponse
from app.schemas.treatment import TreatmentRecommendationRequest, TreatmentRecommendationResponse

router = APIRouter(prefix="/treatments", tags=["treatment-recommendation"])


@router.post("/recommend", response_model=SuccessResponse)
def recommend_treatment(
    request_body: TreatmentRecommendationRequest,
    use_case: GenerateTreatmentUseCase = Depends(get_generate_treatment_use_case),  # noqa: B008
) -> SuccessResponse:
    recommendation = use_case.execute(request_body.model_dump())
    return SuccessResponse(
        data=TreatmentRecommendationResponse(**recommendation),
        meta=MetaResponse(request_id=request_id_context.get()),
    )
