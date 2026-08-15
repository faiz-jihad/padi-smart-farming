from __future__ import annotations

import logging
import uuid
from contextlib import asynccontextmanager

from fastapi import FastAPI, Request
from fastapi.exceptions import RequestValidationError
from fastapi.middleware.cors import CORSMiddleware
from fastapi.responses import JSONResponse
from starlette.exceptions import HTTPException as StarletteHTTPException

from app.api.dependencies import ServiceContainer
from app.api.v1.router import api_router
from app.core.config import get_settings
from app.core.exceptions import AppError
from app.core.logging import configure_logging, request_id_context
from app.schemas.common import ErrorDetail, ErrorResponse, MetaResponse

logger = logging.getLogger(__name__)


@asynccontextmanager
async def lifespan(app: FastAPI):
    settings = get_settings()
    configure_logging(settings.app_debug)
    container = ServiceContainer(settings)
    container.load_startup_resources()
    app.state.container = container
    yield


def create_app() -> FastAPI:
    settings = get_settings()
    app = FastAPI(
        title="P.A.D.I. AI Service",
        description="AI service untuk deteksi penyakit padi dan rekomendasi pendukung keputusan.",
        version=settings.model_version,
        debug=settings.app_debug,
        lifespan=lifespan,
    )
    app.add_middleware(
        CORSMiddleware,
        allow_origins=settings.allowed_origins,
        allow_credentials=True,
        allow_methods=["*"],
        allow_headers=["*"],
    )
    app.middleware("http")(request_id_middleware)
    app.add_exception_handler(AppError, app_error_handler)
    app.add_exception_handler(RequestValidationError, validation_error_handler)
    app.add_exception_handler(StarletteHTTPException, http_error_handler)
    app.add_exception_handler(Exception, unhandled_error_handler)
    app.include_router(api_router, prefix=settings.api_v1_prefix)
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
    return _error_response(exc.status_code, exc.code, exc.message)


async def http_error_handler(request: Request, exc: StarletteHTTPException) -> JSONResponse:
    return _error_response(exc.status_code, "HTTP_ERROR", str(exc.detail))


async def validation_error_handler(request: Request, exc: RequestValidationError) -> JSONResponse:
    return _error_response(422, "REQUEST_VALIDATION_ERROR", "Request tidak valid.")


async def unhandled_error_handler(request: Request, exc: Exception) -> JSONResponse:
    logger.exception("event=unhandled_error")
    return _error_response(500, "INTERNAL_SERVER_ERROR", "Terjadi kesalahan pada server.")


def _error_response(status_code: int, code: str, message: str) -> JSONResponse:
    payload = ErrorResponse(
        error=ErrorDetail(code=code, message=message),
        meta=MetaResponse(request_id=request_id_context.get()),
    )
    return JSONResponse(status_code=status_code, content=payload.model_dump())


app = create_app()
