from __future__ import annotations

from typing import Any, Protocol


class DiseaseModelRepository(Protocol):
    @property
    def is_loaded(self) -> bool:
        """Mengembalikan status model tanpa memuat ulang model."""

    @property
    def model_version(self) -> str:
        """Mengembalikan versi model aktif."""

    def predict(self, image_rgb) -> tuple[str, str, float]:
        """Menghasilkan disease_code, disease_name, dan confidence dari citra RGB."""

    def predict_top(self, image_rgb, top_k: int = 3) -> tuple[str, str, float, list[dict[str, float | str]], float]:
        """Menghasilkan prediksi utama, kandidat teratas, dan margin confidence."""

    def predict_with_embedding(self, image_rgb) -> tuple[str, str, float, Any]:
        """Menghasilkan prediksi sekaligus vektor representasi visual (embedding) daun."""

