from __future__ import annotations

from pydantic import BaseModel, Field


# ── Common ────────────────────────────────────────────────────────────────────


class ErrorDetail(BaseModel):
    code: str
    message: str


class ErrorResponse(BaseModel):
    success: bool = False
    error: ErrorDetail


class MetaResponse(BaseModel):
    request_id: str | None = None


class SuccessResponse(BaseModel):
    success: bool = True
    data: object
    meta: MetaResponse | None = None
