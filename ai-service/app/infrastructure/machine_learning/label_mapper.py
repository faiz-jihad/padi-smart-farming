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

    def normalize_label(self, label_or_code: str) -> str:
        """Menormalisasi label model eksternal menjadi disease_code aplikasi."""
        return self._normalize_code(label_or_code)

    def display_name_for_code(self, disease_code: str) -> str:
        """Mengambil nama tampil dari disease_code aplikasi."""
        return SUPPORTED_DISEASE_CODES.get(disease_code, SUPPORTED_DISEASE_CODES["unknown"])

    def _normalize_code(self, label_or_code: str) -> str:
        value = label_or_code.strip()
        if value in SUPPORTED_DISEASE_CODES:
            return value

        lowered = value.lower()
        if lowered in SUPPORTED_DISEASE_CODES:
            return lowered
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
            "bacterial_blight": "bacterial_leaf_blight",
            "hawar_daun": "bacterial_leaf_blight",
            "bacterial_leaf_streak": "bacterial_leaf_streak",
            "bacterial_panicle_blight": "bacterial_panicle_blight",
            "blast": "blast",
            "leaf_blast": "blast",
            "neck_blast": "blast",
            "rice_blast": "blast",
            "penyakit_blas": "blast",
            "brown_spot": "brown_spot",
            "brown_leaf_spot": "brown_spot",
            "narrow_brown_leaf_spot": "brown_spot",
            "bercak_cokelat": "brown_spot",
            "dead_heart": "dead_heart",
            "penggerek_batang": "dead_heart",
            "downy_mildew": "downy_mildew",
            "bulu_embun": "downy_mildew",
            "hispa": "hispa",
            "normal": "healthy",
            "healthy": "healthy",
            "healthyleaf": "healthy",
            "healthy_leaf": "healthy",
            "padi_sehat": "healthy",
            "tungro": "tungro",
        }
        if slug in aliases:
            return aliases[slug]

        for alias, disease_code in sorted(aliases.items(), key=lambda item: len(item[0]), reverse=True):
            if alias in slug or alias in lowered:
                return disease_code

        return "unknown"
