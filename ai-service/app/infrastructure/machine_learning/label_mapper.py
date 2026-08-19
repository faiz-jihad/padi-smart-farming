from __future__ import annotations

from app.core.constants import SUPPORTED_DISEASE_CODES


class LabelMapper:
    def __init__(self, class_mapping: dict[str, str]) -> None:
        self.class_mapping = class_mapping

    def map_index(self, class_index: int) -> tuple[str, str]:
        """Memetakan index output model ke kode penyakit aplikasi."""
        disease_code = self.class_mapping.get(str(class_index), "unknown")
        if disease_code not in SUPPORTED_DISEASE_CODES:
            disease_code = "unknown"
        return disease_code, SUPPORTED_DISEASE_CODES[disease_code]
