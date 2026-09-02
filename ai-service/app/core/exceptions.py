from __future__ import annotations


class AppError(Exception):
    status_code: int = 400
    code: str = "APP_ERROR"

    def __init__(
        self,
        message: str,
        *,
        code: str | None = None,
        status_code: int | None = None,
    ) -> None:
        self.message = message
        self.code = code or self.__class__.code
        self.status_code = status_code or self.__class__.status_code
        super().__init__(message)


class InvalidImageError(AppError):
    status_code = 422
    code = "INVALID_IMAGE"


class ImageTooLargeError(AppError):
    status_code = 413
    code = "IMAGE_TOO_LARGE"


class ImageTooSmallError(AppError):
    status_code = 422
    code = "IMAGE_TOO_SMALL"


class UnsupportedFormatError(AppError):
    status_code = 415
    code = "UNSUPPORTED_FORMAT"


class ModelUnavailableError(AppError):
    status_code = 503
    code = "MODEL_UNAVAILABLE"


class InferenceFailedError(AppError):
    status_code = 500
    code = "INFERENCE_FAILED"


class ServiceBusyError(AppError):
    status_code = 503
    code = "SERVICE_BUSY"


class ExternalServiceError(AppError):
    status_code = 502
    code = "EXTERNAL_SERVICE_ERROR"


class ExternalServiceTimeoutError(AppError):
    status_code = 504
    code = "EXTERNAL_SERVICE_TIMEOUT"


# Legacy alias — keep for backward compat
ImageValidationError = InvalidImageError

