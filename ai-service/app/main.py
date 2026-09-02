from __future__ import annotations

import logging
import uuid
from contextlib import asynccontextmanager
from pathlib import Path

from fastapi import FastAPI, Request
from fastapi.exceptions import RequestValidationError
from fastapi.middleware.cors import CORSMiddleware
from fastapi.responses import JSONResponse
from starlette.exceptions import HTTPException as StarletteHTTPException

from app.core.config import get_settings
from app.core.exceptions import AppError
from app.core.logging import configure_logging, request_id_context
from app.schemas.common import ErrorDetail, ErrorResponse
from app.services.decision_engine import DecisionEngine
from app.services.disease_catalog import DiseaseCatalog
from app.services.image_quality import ImageQualityService
from app.services.padi_classifier import PADIClassifier

logger = logging.getLogger(__name__)


@asynccontextmanager
async def lifespan(app: FastAPI):
    settings = get_settings()
    configure_logging(settings.app_debug)

    logger.info(
        "event=startup model=%s version=%s",
        settings.model_name,
        settings.model_version,
    )

    # 1. PADIClassifier — load, validate, warmup
    classifier = PADIClassifier(
        model_path=settings.model_path,
        model_name=settings.model_name,
        model_version=settings.model_version,
        model_imgsz=settings.model_imgsz,
        max_concurrency=settings.max_gpu_concurrency,
    )
    classifier.load_and_validate()  # Gagal keras jika model tidak valid
    app.state.classifier = classifier

    # 2. Image Quality Service
    app.state.quality_service = ImageQualityService(
        max_size_bytes=settings.max_image_size_bytes,
        blur_threshold=settings.quality_blur_threshold,
        brightness_min=settings.quality_brightness_min,
        brightness_max=settings.quality_brightness_max,
        min_width=settings.min_image_width,
        min_height=settings.min_image_height,
    )

    # 3. Decision Engine
    app.state.decision_engine = DecisionEngine(
        high_confidence_threshold=settings.high_confidence_threshold,
        review_confidence_threshold=settings.review_confidence_threshold,
        min_margin_threshold=settings.min_margin_threshold,
    )

    # 4. Disease Catalog
    app.state.disease_catalog = DiseaseCatalog(catalog_path=settings.disease_catalog_path)

    logger.info("event=startup_complete model_loaded=%s", classifier.is_loaded)

    yield

    logger.info("event=shutdown")


def create_app() -> FastAPI:
    settings = get_settings()

    app = FastAPI(
        title="P.A.D.I. AI Service",
        description=(
            "Production AI service untuk diagnosa penyakit padi berbasis YOLO classify. "
            "Confidence tinggi tidak menjamin diagnosis pasti — selalu libatkan PPL untuk verifikasi lapangan."
        ),
        version=f"{settings.model_name}_{settings.model_version}",
        debug=settings.app_debug,
        lifespan=lifespan,
        docs_url="/docs",
        redoc_url="/redoc",
    )

    # CORS — tidak allow * di production
    app.add_middleware(
        CORSMiddleware,
        allow_origins=settings.allowed_origins,
        allow_credentials=True,
        allow_methods=["*"],
        allow_headers=["*"],
    )

    # Request ID middleware
    app.middleware("http")(request_id_middleware)

    # Error handlers
    app.add_exception_handler(AppError, app_error_handler)  # type: ignore[arg-type]
    app.add_exception_handler(RequestValidationError, validation_error_handler)  # type: ignore[arg-type]
    app.add_exception_handler(StarletteHTTPException, http_error_handler)  # type: ignore[arg-type]
    app.add_exception_handler(Exception, unhandled_error_handler)  # type: ignore[arg-type]

    # Routers — semua endpoint didaftarkan di sini (bukan di api_v1_prefix terpisah)
    from app.api.v1.endpoints.diagnosis import router as diagnosis_router
    app.include_router(diagnosis_router)

    return app


async def request_id_middleware(request: Request, call_next):
    request_id = request.headers.get("X-Request-ID") or str(uuid.uuid4())
    token = request_id_context.set(request_id)
    try:
        response = await call_next(request)
        response.headers["X-Request-ID"] = request_id
        return response
    finally:
        request_id_context.reset(token)


async def app_error_handler(request: Request, exc: AppError) -> JSONResponse:
    logger.warning(
        "event=app_error code=%s message=%s request_id=%s",
        exc.code,
        exc.message,
        request_id_context.get(),
    )
    return _error_response(exc.status_code, exc.code, exc.message)


async def http_error_handler(request: Request, exc: StarletteHTTPException) -> JSONResponse:
    return _error_response(exc.status_code, "HTTP_ERROR", str(exc.detail))


async def validation_error_handler(request: Request, exc: RequestValidationError) -> JSONResponse:
    return _error_response(422, "REQUEST_VALIDATION_ERROR", "Request tidak valid.")


async def unhandled_error_handler(request: Request, exc: Exception) -> JSONResponse:
    logger.exception("event=unhandled_error")
    return _error_response(500, "INTERNAL_SERVER_ERROR", "Terjadi kesalahan pada server.")


def _error_response(status_code: int, code: str, message: str) -> JSONResponse:
    payload = ErrorResponse(error=ErrorDetail(code=code, message=message))
    return JSONResponse(status_code=status_code, content=payload.model_dump())


app = create_app()
