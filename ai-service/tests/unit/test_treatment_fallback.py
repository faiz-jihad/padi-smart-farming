from app.core.exceptions import ExternalServiceTimeoutError
from app.infrastructure.llm.treatment_generator import TreatmentGenerator


class TimeoutLlmClient:
    is_configured = True

    def simplify_recommendation(self, prompt):
        raise ExternalServiceTimeoutError("timeout", code="LLM_TIMEOUT")


def test_treatment_generator_falls_back_to_knowledge_base_when_llm_fails():
    generator = TreatmentGenerator(TimeoutLlmClient())
    guideline = {
        "name": "Blast",
        "immediate_actions": ["Pantau bercak."],
        "prevention_steps": ["Hindari nitrogen berlebih."],
        "danger_signs": ["Serangan menyebar cepat."],
        "extension_officer_advice": "Hubungi penyuluh.",
    }

    recommendation = generator.generate(
        guideline,
        {"disease_code": "blast", "confidence": 0.91, "severity": "medium", "affected_area_percentage": 12},
    )

    assert recommendation["immediate_actions"] == ["Pantau bercak."]
    assert recommendation["condition_summary"].startswith("Gejala mengarah ke Blast")
