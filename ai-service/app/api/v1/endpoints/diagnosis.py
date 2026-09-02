from __future__ import annotations

import logging
import uuid

from fastapi import APIRouter, File, Form, Request, UploadFile
from fastapi.responses import JSONResponse

from app.schemas.diagnosis import (
    DecisionResult,
    DiagnosisData,
    DiagnosisResponse,
    DiseaseInfo,
    FeedbackRequest,
    FeedbackResponse,
    HealthDetailResponse,
    HealthSimpleResponse,
    ModelInfoResponse,
    ModelMeta,
    PredictionResult,
    QualityResult,
    SecondPrediction,
    TopPredictionItem,
)

logger = logging.getLogger(__name__)

router = APIRouter()


# ─── Health ──────────────────────────────────────────────────────────────────


@router.get("/health", response_model=HealthSimpleResponse, tags=["health"])
async def health_simple() -> HealthSimpleResponse:
    """Simple health check untuk load balancer / uptime monitor."""
    return HealthSimpleResponse(status="ok")


@router.get(
    "/api/v1/ai/health",
    response_model=HealthDetailResponse,
    tags=["health"],
)
async def health_detail(request: Request) -> HealthDetailResponse:
    """Detail health check — status model dan device."""
    classifier = request.app.state.classifier
    return HealthDetailResponse(
        status="healthy" if classifier.is_loaded else "degraded",
        model_loaded=classifier.is_loaded,
        device=classifier.device if classifier.is_loaded else "unknown",
        model_version=classifier.model_version,
        model_name=classifier.model_name,
    )


# ─── Model Info ──────────────────────────────────────────────────────────────


@router.get(
    "/api/v1/ai/model/info",
    response_model=ModelInfoResponse,
    tags=["model"],
)
async def model_info(request: Request) -> ModelInfoResponse:
    """Return model metadata. Filesystem path tidak diekspos."""
    classifier = request.app.state.classifier
    info = classifier.get_model_info()
    return ModelInfoResponse(**info)


# ─── Diagnosis ───────────────────────────────────────────────────────────────


@router.post(
    "/api/v1/ai/padi/diagnose",
    response_model=DiagnosisResponse,
    tags=["diagnosis"],
    summary="Diagnosa penyakit padi dari foto daun",
)
async def diagnose(
    request: Request,
    image: UploadFile = File(...),  # noqa: B008
    field_id: str | None = Form(default=None),  # noqa: B008
    farmer_id: str | None = Form(default=None),  # noqa: B008
    latitude: float | None = Form(default=None),  # noqa: B008
    longitude: float | None = Form(default=None),  # noqa: B008
    hst: int | None = Form(default=None),  # noqa: B008
    growth_stage: str | None = Form(default=None),  # noqa: B008
    locale: str = Form(default="id"),  # noqa: B008
) -> DiagnosisResponse:
    """
    POST /api/v1/ai/padi/diagnose

    Terima foto daun padi dan hasilkan:
    - Prediksi penyakit (top 1-3)
    - Decision status
    - Quality assessment
    - Informasi penyakit dari catalog

    Metadata (field_id, hst, dll.) tidak mempengaruhi class prediction.
    Prediction berasal murni dari image classifier.
    """
    quality_svc = request.app.state.quality_service
    classifier = request.app.state.classifier
    decision_engine = request.app.state.decision_engine
    catalog = request.app.state.disease_catalog

    # 1. Baca upload
    content = await image.read()

    # 2. Validasi upload (format, ukuran)
    quality_svc.validate_upload(
        content=content,
        content_type=image.content_type,
        filename=image.filename,
    )

    # 3. Decode image + EXIF fix
    image_rgb = quality_svc.decode_image(content)

    # 4. Quality assessment
    quality_report = quality_svc.assess_quality(image_rgb)

    # 5. Jika INVALID → tolak inference
    if quality_report.status == "INVALID":
        # Masih buat response terstruktur dengan RETAKE_PHOTO
        pass  # Decision engine menangani ini

    # 6. Inference (skip jika INVALID karena decision akan RETAKE_PHOTO)
    if quality_report.status != "INVALID":
        classifier_result = await classifier.classify(image_rgb)
    else:
        # Buat dummy result untuk RETAKE_PHOTO path
        from app.services.padi_classifier import ClassifierResult, PredictionCandidate
        classifier_result = ClassifierResult(
            request_id=str(uuid.uuid4()),
            class_id=-1,
            class_name="unknown",
            confidence=0.0,
            confidence_percent=0.0,
            margin=0.0,
            top_predictions=[],
            preprocessing_ms=0.0,
            inference_ms=0.0,
            total_latency_ms=0.0,
            model_name=classifier.model_name,
            model_version=classifier.model_version,
            model_task="classify",
            model_imgsz=classifier.model_imgsz,
            device=classifier.device,
        )

    # 7. Decision
    decision_result = decision_engine.decide(classifier_result, quality_report)

    # 8. Log
    logger.info(
        "event=diagnosis request_id=%s class=%s conf=%.4f decision=%s quality=%s "
        "field_id=%s hst=%s latency_ms=%.1f",
        classifier_result.request_id,
        classifier_result.class_name,
        classifier_result.confidence,
        decision_result.status,
        quality_report.status,
        field_id,
        hst,
        classifier_result.total_latency_ms,
    )

    # 9. Catalog
    class_name = classifier_result.class_name if quality_report.status != "INVALID" else "normal"
    disease_info = catalog.get_disease_info(class_name, locale=locale)
    display_name = catalog.get_display_name(class_name, locale=locale)

    # 10. Build response
    top_preds = [
        TopPredictionItem(
            rank=p.rank,
            class_name=p.class_name,
            confidence=p.confidence,
        )
        for p in classifier_result.top_predictions
    ]

    second_pred = None
    if len(classifier_result.top_predictions) > 1:
        p2 = classifier_result.top_predictions[1]
        second_pred = SecondPrediction(
            class_name=p2.class_name,
            confidence=p2.confidence,
        )

    return DiagnosisResponse(
        data=DiagnosisData(
            request_id=classifier_result.request_id,
            diagnosis_id=str(uuid.uuid4()),
            model=ModelMeta(
                name=classifier_result.model_name,
                version=classifier_result.model_version,
                task=classifier_result.model_task,
                imgsz=classifier_result.model_imgsz,
            ),
            prediction=PredictionResult(
                class_id=classifier_result.class_id,
                class_name=classifier_result.class_name,
                display_name=display_name,
                confidence=classifier_result.confidence,
                confidence_percent=classifier_result.confidence_percent,
                second_prediction=second_pred,
                margin=classifier_result.margin,
                top_predictions=top_preds,
            ),
            decision=DecisionResult(
                status=decision_result.status,
                needs_ppl_review=decision_result.needs_ppl_review,
                needs_retake=decision_result.needs_retake,
                needs_differential_review=decision_result.needs_differential_review,
            ),
            quality=QualityResult(
                status=quality_report.status,
                blur_score=quality_report.blur_score,
                brightness=quality_report.brightness,
                width=quality_report.width,
                height=quality_report.height,
                issues=quality_report.issues,
            ),
            disease=DiseaseInfo(**disease_info),
            latency_ms=classifier_result.total_latency_ms,
        )
    )


# ─── Feedback ─────────────────────────────────────────────────────────────────


@router.post(
    "/api/v1/ai/padi/feedback",
    response_model=FeedbackResponse,
    tags=["feedback"],
    summary="Submit PPL/agronomist correction untuk diagnosis AI",
)
async def submit_feedback(
    request: Request,
    feedback: FeedbackRequest,
) -> FeedbackResponse:
    """
    POST /api/v1/ai/padi/feedback

    Simpan feedback verifikasi PPL untuk:
    - Hard-example mining
    - Future retraining dataset
    - Model monitoring

    AI Service tidak langsung retrain dari feedback ini.
    """
    feedback_id = str(uuid.uuid4())

    # Log feedback untuk persistence/monitoring downstream
    logger.info(
        "event=feedback_received feedback_id=%s diagnosis_id=%s "
        "ai_pred=%s verified=%s source=%s verified_by=%s",
        feedback_id,
        feedback.diagnosis_id,
        feedback.ai_prediction,
        feedback.verified_label,
        feedback.verification_source,
        feedback.verified_by,
    )

    # TODO: tambahkan persistence ke database/file jika diperlukan
    # Saat ini feedback dicatat via structured logging untuk downstream processing

    return FeedbackResponse(
        feedback_id=feedback_id,
        message=(
            "Feedback diterima. Terima kasih atas verifikasi lapangan Anda. "
            "Data ini akan digunakan untuk peningkatan model AI berikutnya."
        ),
    )
