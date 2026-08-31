from __future__ import annotations

from dataclasses import dataclass, field


@dataclass(frozen=True)
class LeafValidationDecision:
    is_acceptable: bool
    leaf_ratio: float
    warnings: list[str] = field(default_factory=list)
    error_code: str | None = None
    error_message: str | None = None


class LeafValidationPolicy:
    """Kebijakan untuk memvalidasi apakah gambar adalah daun tanaman padi."""

    def __init__(
        self,
        min_leaf_ratio: float = 0.12,
        min_disease_confidence: float = 0.35,
    ) -> None:
        self.min_leaf_ratio = min_leaf_ratio
        self.min_disease_confidence = min_disease_confidence

    def evaluate_visual_features(
        self,
        leaf_ratio: float,
        skin_ratio: float,
        mean_saturation: float,
        unnatural_ratio: float,
    ) -> LeafValidationDecision:
        """Mengevaluasi karakteristik warna dan tekstur citra untuk mendeteksi daun."""
        warnings: list[str] = []

        # 1. Deteksi objek manusia / kulit (wajah, selfie, tangan dominan)
        if skin_ratio > 0.25 and leaf_ratio < 0.20:
            return LeafValidationDecision(
                is_acceptable=False,
                leaf_ratio=leaf_ratio,
                error_code="IMAGE_NOT_LEAF_HUMAN",
                error_message="Foto terdeteksi memuat wajah atau tubuh manusia, bukan daun padi. Harap foto daun tanaman padi.",
            )

        # 2. Deteksi citra monokrom, kertas dokumen, atau permukaan abu-abu
        if mean_saturation < 15.0 and leaf_ratio < 0.10:
            return LeafValidationDecision(
                is_acceptable=False,
                leaf_ratio=leaf_ratio,
                error_code="IMAGE_NOT_LEAF_MONOCHROME",
                error_message="Gambar monokrom atau dokumen terdeteksi. Harap ambil foto daun padi asli di lapangan.",
            )

        # 3. Deteksi warna sintetis tidak wajar (misal: dominasi biru/cyan/ungu buatan)
        if unnatural_ratio > 0.45 and leaf_ratio < 0.15:
            return LeafValidationDecision(
                is_acceptable=False,
                leaf_ratio=leaf_ratio,
                error_code="IMAGE_NOT_LEAF",
                error_message="Objek pada gambar bukan daun padi. Harap arahkan kamera ke daun padi.",
            )

        # 4. Deteksi ambang batas minimum spektrum daun (hijau atau lesi penyakit kuning/cokelat)
        if leaf_ratio < self.min_leaf_ratio:
            return LeafValidationDecision(
                is_acceptable=False,
                leaf_ratio=leaf_ratio,
                error_code="IMAGE_NOT_LEAF",
                error_message="Objek pada gambar bukan daun padi. Harap pastikan kamera berfokus pada daun tanaman padi.",
            )

        if leaf_ratio < 0.25:
            warnings.append("Porsi daun pada gambar cukup kecil. Disarankan mengambil foto daun lebih dekat.")

        return LeafValidationDecision(
            is_acceptable=True,
            leaf_ratio=leaf_ratio,
            warnings=warnings,
        )

    def evaluate_model_confidence(self, confidence: float, leaf_ratio: float) -> LeafValidationDecision:
        """Memvalidasi keyakinan model terhadap pola penyakit daun padi."""
        # Jika confidence model sangat rendah atau pola daun tidak meyakinkan
        if confidence < self.min_disease_confidence:
            return LeafValidationDecision(
                is_acceptable=False,
                leaf_ratio=leaf_ratio,
                error_code="IMAGE_NOT_LEAF_UNRECOGNIZED",
                error_message="Pola daun padi tidak teridentifikasi dengan jelas. Pastikan foto memperlihatkan daun padi yang jelas dan fokus.",
            )

        return LeafValidationDecision(
            is_acceptable=True,
            leaf_ratio=leaf_ratio,
        )
