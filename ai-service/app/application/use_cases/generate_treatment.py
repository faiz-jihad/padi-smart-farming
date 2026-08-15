from __future__ import annotations

from app.core.constants import DISCLAIMER_TEXT
from app.infrastructure.llm.treatment_generator import TreatmentGenerator
from app.infrastructure.persistence.knowledge_base_repository import KnowledgeBaseRepository


class GenerateTreatmentUseCase:
    def __init__(
        self,
        knowledge_base_repository: KnowledgeBaseRepository,
        treatment_generator: TreatmentGenerator,
    ) -> None:
        self.knowledge_base_repository = knowledge_base_repository
        self.treatment_generator = treatment_generator

    def execute(self, request_data: dict) -> dict:
        """Menyusun rekomendasi penanganan berbasis knowledge base tervalidasi."""
        disease_code = request_data["disease_code"]
        guideline = self.knowledge_base_repository.get_treatment_guideline(disease_code)
        recommendation = self.treatment_generator.generate(guideline, request_data)
        recommendation["disclaimer"] = DISCLAIMER_TEXT
        recommendation["sources"] = guideline.get("sources", [])
        return recommendation
