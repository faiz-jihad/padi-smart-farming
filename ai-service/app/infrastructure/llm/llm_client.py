from __future__ import annotations

import httpx

from app.core.exceptions import ExternalServiceError, ExternalServiceTimeoutError


class LlmClient:
    def __init__(self, api_key: str, model: str, base_url: str, timeout_seconds: float) -> None:
        self.api_key = api_key
        self.model = model
        self.base_url = base_url
        self.timeout_seconds = timeout_seconds

    @property
    def is_configured(self) -> bool:
        return bool(self.api_key and self.model and self.base_url)

    def simplify_recommendation(self, prompt: str) -> str:
        """Memanggil LLM provider opsional untuk menyederhanakan bahasa rekomendasi."""
        if not self.is_configured:
            raise ExternalServiceError("LLM tidak dikonfigurasi.", code="LLM_NOT_CONFIGURED")
        try:
            response = httpx.post(
                self.base_url,
                headers={"Authorization": f"Bearer {self.api_key}"},
                json={"model": self.model, "prompt": prompt},
                timeout=self.timeout_seconds,
            )
            response.raise_for_status()
            payload = response.json()
            return str(payload.get("text", "")).strip()
        except httpx.TimeoutException as exc:
            raise ExternalServiceTimeoutError("LLM timeout.", code="LLM_TIMEOUT") from exc
        except httpx.HTTPError as exc:
            raise ExternalServiceError("LLM gagal menghasilkan rekomendasi.", code="LLM_REQUEST_FAILED") from exc
