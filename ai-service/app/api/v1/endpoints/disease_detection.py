from __future__ import annotations

from fastapi import APIRouter, Depends, File, Form, UploadFile

from app.api.dependencies import get_detect_disease_use_case
from app.application.dto.disease_detection_dto import DiseaseDetectionInput
from app.application.use_cases.detect_disease import DetectDiseaseUseCase
from app.core.logging import request_id_context
from app.schemas.common import MetaResponse, SuccessResponse
from app.schemas.disease import DiseaseDetectionResponse, ImageQualityResponse, PredictionCandidateResponse

router = APIRouter(prefix="/diseases", tags=["disease-detection"])


@router.post("/detect", response_model=SuccessResponse)
async def detect_disease(
    image: UploadFile = File(...),  # noqa: B008
    plant_age_days: int | None = Form(default=None),  # noqa: B008
    latitude: float | None = Form(default=None),  # noqa: B008
    longitude: float | None = Form(default=None),  # noqa: B008
    use_case: DetectDiseaseUseCase = Depends(get_detect_disease_use_case),  # noqa: B008
) -> SuccessResponse:
    content = await image.read()
    prediction = use_case.execute(
        DiseaseDetectionInput(
            content=content,
            content_type=image.content_type,
            filename=image.filename,
            plant_age_days=plant_age_days,
            latitude=latitude,
            longitude=longitude,
        )
    )
    return SuccessResponse(
        data=DiseaseDetectionResponse(
            disease_code=prediction.disease_code,
            disease_name=prediction.disease_name,
            confidence=prediction.confidence,
            confidence_level=prediction.confidence_level,
            image_quality=ImageQualityResponse(**prediction.image_quality.__dict__),
            needs_expert_review=prediction.needs_expert_review,
            model_version=prediction.model_version,
            processing_time_ms=prediction.processing_time_ms,
            top_predictions=[
                PredictionCandidateResponse(**candidate.__dict__)
                for candidate in prediction.top_predictions
            ],
            prediction_margin=prediction.prediction_margin,
            model_accuracy=prediction.model_accuracy,
            detection_status=prediction.detection_status,
            status_message=prediction.status_message,
        ),
        meta=MetaResponse(request_id=request_id_context.get()),
    )
