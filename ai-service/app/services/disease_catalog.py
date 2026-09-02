from __future__ import annotations

import json
import logging
from pathlib import Path

logger = logging.getLogger(__name__)

DISPLAY_NAMES_EN: dict[str, str] = {
    "bacterial_leaf_blight": "Bacterial Leaf Blight",
    "bacterial_leaf_streak": "Bacterial Leaf Streak",
    "bacterial_panicle_blight": "Bacterial Panicle Blight",
    "blast": "Blast",
    "brown_spot": "Brown Spot",
    "dead_heart": "Dead Heart",
    "downy_mildew": "Downy Mildew",
    "hispa": "Rice Hispa",
    "normal": "Normal",
    "tungro": "Tungro",
}

DISPLAY_NAMES_ID: dict[str, str] = {
    "bacterial_leaf_blight": "Hawar Daun Bakteri",
    "bacterial_leaf_streak": "Hawar Streak Bakteri",
    "bacterial_panicle_blight": "Hawar Malai Bakteri",
    "blast": "Blast",
    "brown_spot": "Bercak Cokelat",
    "dead_heart": "Dead Heart",
    "downy_mildew": "Downy Mildew",
    "hispa": "Rice Hispa",
    "normal": "Normal",
    "tungro": "Tungro",
}

# Normal class pesan khusus — jangan klaim "pasti sehat"
NORMAL_SAFETY_MESSAGE: dict[str, str] = {
    "id": "Tidak ditemukan pola penyakit dominan berdasarkan foto ini.",
    "en": "No dominant disease pattern detected based on this photo.",
}


class DiseaseCatalog:
    """
    Catalog penyakit padi yang memuat deskripsi, gejala, dan rekomendasi tindakan.

    Rekomendasi berasal dari kurated catalog — bukan generasi AI.
    Treatment harus divalidasi oleh PPL/agronomist.
    """

    def __init__(self, catalog_path: Path) -> None:
        self._catalog: dict[str, dict] = {}
        self._load(catalog_path)

    def _load(self, path: Path) -> None:
        if not path.exists():
            logger.warning(
                "event=catalog_not_found path=%s using_empty_catalog=true", path
            )
            return
        try:
            data = json.loads(path.read_text(encoding="utf-8"))
            self._catalog = {str(k): v for k, v in data.items()}
            logger.info(
                "event=catalog_loaded num_entries=%d path=%s",
                len(self._catalog),
                path,
            )
        except (json.JSONDecodeError, OSError) as exc:
            logger.error("event=catalog_load_failed path=%s error=%s", path, exc)

    def get_display_name(self, class_name: str, locale: str = "en") -> str:
        """Return user-friendly display name."""
        if locale == "id":
            return DISPLAY_NAMES_ID.get(class_name, class_name)
        return DISPLAY_NAMES_EN.get(class_name, class_name)

    def get_disease_info(self, class_name: str, locale: str = "en") -> dict:
        """
        Return disease info untuk response JSON.
        Kelas 'normal' mempunyai safety message khusus.
        """
        locale = locale if locale in ("id", "en") else "en"

        entry = self._catalog.get(class_name, {})
        display_name = self.get_display_name(class_name, locale)

        if class_name == "normal":
            description = NORMAL_SAFETY_MESSAGE.get(locale, NORMAL_SAFETY_MESSAGE["en"])
        else:
            desc_map = entry.get("description", {})
            description = desc_map.get(locale) or desc_map.get("en") or ""

        actions_map = entry.get("recommended_actions", {})
        recommended_actions: list[str] = (
            actions_map.get(locale) or actions_map.get("en") or []
        )

        severity_map = entry.get("severity_note", {})
        severity_note = severity_map.get(locale) or severity_map.get("en") or ""

        symptoms_map = entry.get("symptoms", {})
        symptoms: list[str] = symptoms_map.get(locale) or symptoms_map.get("en") or []

        return {
            "name": display_name,
            "class_name": class_name,
            "description": description,
            "symptoms": symptoms,
            "recommended_actions": recommended_actions,
            "severity_note": severity_note,
        }
