from __future__ import annotations


class AppError(Exception):
    status_code = 400
    code = "APP_ERROR"

    def __init__(self, message: str, *, code: str | None = None, status_code: int | None = None) -> None:
        self.message = message
        self.code = code or self.code
        self.status_code = status_code or self.status_code
        super().__init__(message)


class ImageValidationError(AppError):
    status_code = 422


class ModelUnavailableError(AppError):
    status_code = 503
    code = "MODEL_UNAVAILABLE"


class ExternalServiceError(AppError):
    status_code = 502


class ExternalServiceTimeoutError(AppError):
    status_code = 504
    code = "EXTERNAL_SERVICE_TIMEOUT"
