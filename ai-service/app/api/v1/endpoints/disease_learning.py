from __future__ import annotations

from fastapi import APIRouter, Depends, File, Form, UploadFile

from app.api.dependencies import get_container, get_learn_disease_scan_use_case
from app.application.use_cases.learn_disease_scan import LearnDiseaseScanUseCase
from app.core.logging import request_id_context
from app.schemas.common import MetaResponse, SuccessResponse

router = APIRouter(prefix="/diseases", tags=["disease-learning"])


@router.post("/learn", response_model=SuccessResponse)
async def learn_disease_scan(
    image: UploadFile = File(...),  # noqa: B008
    disease_code: str = Form(...),  # noqa: B008
    disease_name: str = Form(...),  # noqa: B008
    confidence: float = Form(default=1.0),  # noqa: B008
    source: str = Form(default="farmer_confirmed"),  # noqa: B008
    sample_id: str | None = Form(default=None),  # noqa: B008
    use_case: LearnDiseaseScanUseCase = Depends(get_learn_disease_scan_use_case),  # noqa: B008
) -> SuccessResponse:
    """Mendaftarkan sampel daun terkonfirmasi/terverifikasi agar sistem belajar dari daun tersebut."""
    content = await image.read()
    learned_sample = use_case.execute(
        content=content,
        disease_code=disease_code,
        disease_name=disease_name,
        confidence=confidence,
        source=source,
        sample_id=sample_id,
    )
    return SuccessResponse(
        data={
            "sample_id": learned_sample.sample_id,
            "disease_code": learned_sample.disease_code,
            "disease_name": learned_sample.disease_name,
            "source": learned_sample.source,
            "learned_at": learned_sample.learned_at,
            "status": "learned_successfully",
        },
        meta=MetaResponse(request_id=request_id_context.get()),
    )


@router.get("/memory-stats", response_model=SuccessResponse)
async def get_memory_stats(
    container=Depends(get_container),  # noqa: B008
) -> SuccessResponse:
    """Mengambil statistik memori daun yang telah dipelajari sistem."""
    stats = container.leaf_memory_bank.get_stats()
    return SuccessResponse(
        data=stats,
        meta=MetaResponse(request_id=request_id_context.get()),
    )
