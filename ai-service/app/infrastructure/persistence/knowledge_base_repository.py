from __future__ import annotations

import json
from pathlib import Path

from app.core.exceptions import AppError


class KnowledgeBaseRepository:
    def __init__(self, guidelines_path: Path) -> None:
        self.guidelines_path = guidelines_path
        self._guidelines: dict[str, dict] | None = None

    def get_treatment_guideline(self, disease_code: str) -> dict:
        """Mengambil panduan penanganan penyakit dari file knowledge base."""
        guidelines = self._load_guidelines()
        clean_code = disease_code.strip().strip("_").lower()
        guideline = guidelines.get(clean_code)
        if guideline is None:
            for key, val in guidelines.items():
                if key in clean_code or clean_code in key:
                    return val
            guideline = guidelines.get("unknown")

        if guideline is None:
            raise AppError(
                "Kode penyakit tidak ditemukan di knowledge base.", code="DISEASE_GUIDELINE_NOT_FOUND", status_code=404
            )
        return guideline

    def _load_guidelines(self) -> dict[str, dict]:
        if self._guidelines is None:
            path = self.guidelines_path
            if not path.exists():
                candidates = [
                    Path.cwd() / "knowledge_base" / "treatment_guidelines.json",
                    Path.cwd() / "ai-service" / "knowledge_base" / "treatment_guidelines.json",
                    Path(__file__).resolve().parents[3] / "knowledge_base" / "treatment_guidelines.json",
                ]
                for candidate in candidates:
                    if candidate.exists():
                        path = candidate
                        break
            with path.open("r", encoding="utf-8") as guideline_file:
                self._guidelines = json.load(guideline_file)
        return self._guidelines
