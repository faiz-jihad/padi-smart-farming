from __future__ import annotations

from typing import Any

from pydantic import BaseModel, Field


class MetaResponse(BaseModel):
    request_id: str


class ErrorDetail(BaseModel):
    code: str
    message: str


class ErrorResponse(BaseModel):
    success: bool = False
    error: ErrorDetail
    meta: MetaResponse


class SuccessResponse(BaseModel):
    success: bool = True
    data: Any
    meta: MetaResponse


class HealthResponse(BaseModel):
    status: str
    service: str
    model_loaded: bool
    model_version: str
    model_error: str | None = Field(default=None)
    python_version: str
