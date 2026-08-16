from __future__ import annotations

import logging

from app.core.exceptions import AppError
from app.infrastructure.llm.llm_client import LlmClient

logger = logging.getLogger(__name__)


class TreatmentGenerator:
    def __init__(self, llm_client: LlmClient) -> None:
        self.llm_client = llm_client

    def generate(self, guideline: dict, request_data: dict) -> dict:
        """Menggabungkan rekomendasi KB dengan penyederhanaan LLM opsional."""
        base_recommendation = self._build_base_recommendation(guideline, request_data)
        if not self.llm_client.is_configured:
            return base_recommendation

        try:
            simplified_summary = self.llm_client.simplify_recommendation(self._build_prompt(guideline, request_data))
            if simplified_summary:
                base_recommendation["condition_summary"] = simplified_summary
        except AppError as exc:
            logger.warning("event=llm_fallback code=%s", exc.code)

        return base_recommendation

    def _build_base_recommendation(self, guideline: dict, request_data: dict) -> dict:
        disease_name = guideline.get("name", request_data["disease_code"])
        confidence = request_data.get("confidence")
        severity = request_data.get("severity")
        condition_summary = (
            f"Gejala mengarah ke {disease_name} dengan confidence {confidence:.2f} dan tingkat keparahan {severity}."
        )
        return {
            "condition_summary": condition_summary,
            "immediate_actions": guideline.get("immediate_actions", []),
            "prevention_steps": guideline.get("prevention_steps", []),
            "danger_signs": guideline.get("danger_signs", []),
            "extension_officer_advice": guideline.get("extension_officer_advice", ""),
        }

    def _build_prompt(self, guideline: dict, request_data: dict) -> str:
        # Prompt dibatasi agar LLM tidak mengubah diagnosis atau membuat dosis baru.
        return (
            "Sederhanakan ringkasan kondisi untuk petani padi. "
            "Jangan ubah diagnosis, jangan tambahkan dosis pestisida, dan gunakan informasi berikut: "
            f"penyakit={guideline.get('name')}, confidence={request_data.get('confidence')}, "
            f"severity={request_data.get('severity')}, area={request_data.get('affected_area_percentage')}%."
        )
