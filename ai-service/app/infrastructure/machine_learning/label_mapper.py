from __future__ import annotations

from app.core.constants import SUPPORTED_DISEASE_CODES


class LabelMapper:
    def __init__(self, class_mapping: dict[str, str]) -> None:
        self.class_mapping = {
            str(index): self._normalize_code(label_or_code)
            for index, label_or_code in class_mapping.items()
        }

    def map_index(self, class_index: int) -> tuple[str, str]:
        """Memetakan index output model ke kode penyakit aplikasi."""
        disease_code = self.class_mapping.get(str(class_index), "unknown")
        if disease_code not in SUPPORTED_DISEASE_CODES:
            disease_code = "unknown"
        return disease_code, SUPPORTED_DISEASE_CODES[disease_code]

    def _normalize_code(self, label_or_code: str) -> str:
        value = label_or_code.strip()
        if value in SUPPORTED_DISEASE_CODES:
            return value

        lowered = value.lower()
        for disease_code, disease_name in SUPPORTED_DISEASE_CODES.items():
            if disease_code == "unknown":
                continue
            if lowered == disease_name.lower():
                return disease_code

        slug = lowered.split("(", 1)[0]
        slug = "".join(character if character.isalnum() else "_" for character in slug)
        slug = "_".join(part for part in slug.split("_") if part)
        aliases = {
            "bacterial_leaf_blight": "bacterial_leaf_blight",
            "bacterial_leaf_streak": "bacterial_leaf_streak",
            "bacterial_panicle_blight": "bacterial_panicle_blight",
            "blast": "blast",
            "penyakit_blas": "blast",
            "brown_spot": "brown_spot",
            "bercak_cokelat": "brown_spot",
            "dead_heart": "dead_heart",
            "penggerek_batang": "dead_heart",
            "downy_mildew": "downy_mildew",
            "bulu_embun": "downy_mildew",
            "hispa": "hispa",
            "normal": "healthy",
            "healthy": "healthy",
            "padi_sehat": "healthy",
            "tungro": "tungro",
        }
        return aliases.get(slug, "unknown")
