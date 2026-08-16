from __future__ import annotations

from dataclasses import dataclass


@dataclass(frozen=True)
class ConfidenceDecision:
    level: str
    needs_expert_review: bool


class ConfidencePolicy:
    def __init__(self, high_threshold: float, medium_threshold: float) -> None:
        if medium_threshold >= high_threshold:
            raise ValueError("MODEL_CONFIDENCE_MEDIUM harus lebih kecil dari MODEL_CONFIDENCE_HIGH")
        self.high_threshold = high_threshold
        self.medium_threshold = medium_threshold

    def evaluate(self, confidence: float) -> ConfidenceDecision:
        """Menentukan level confidence dan kebutuhan review ahli."""
        if confidence >= self.high_threshold:
            return ConfidenceDecision(level="high", needs_expert_review=False)
        if confidence >= self.medium_threshold:
            return ConfidenceDecision(level="medium", needs_expert_review=False)
        return ConfidenceDecision(level="low", needs_expert_review=True)
