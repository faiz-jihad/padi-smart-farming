from __future__ import annotations

from dataclasses import dataclass


@dataclass(frozen=True)
class DiseaseDetectionInput:
    content: bytes
    content_type: str | None
    filename: str | None
    plant_age_days: int | None = None
    latitude: float | None = None
    longitude: float | None = None
