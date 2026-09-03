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
    """Kebijakan untuk memvalidasi apakah gambar adalah daun tanaman padi.

    Threshold dirancang untuk kondisi lapangan nyata:
    - Petani mengambil foto dari jarak ~30-50cm dengan latar belakang sawah.
    - Pencahayaan bervariasi (pagi cerah, siang terang, sore agak gelap).
    - Daun padi sehat berwarna hijau; daun berpenyakit bisa kuning/cokelat/hawar.
    - Latar belakang bisa berupa tanah, air, jerami kering.
    """

    def __init__(
        self,
        min_leaf_ratio: float = 0.18,
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
        green_ratio: float | None = None,
    ) -> LeafValidationDecision:
        """Mengevaluasi karakteristik warna dan tekstur citra untuk mendeteksi daun.

        Urutan evaluasi (dari yang paling kritis ke paling lunak):
        1. Deteksi kulit manusia dominan (selfie, wajah, tangan)
        2. Deteksi gambar monokrom (dokumen, kertas, semen)
        3. Deteksi warna sintetis buatan (layar, poster, cat biru/ungu)
        4. Validasi ambang batas minimum spektrum daun
        """
        warnings: list[str] = []
        green_ratio = leaf_ratio if green_ratio is None else green_ratio

        # 1. Deteksi kulit manusia dominan (wajah, selfie, tangan, tubuh)
        #    Pada daun padi asli: green_ratio (0.30 - 0.95) JAUH lebih tinggi dari skin_ratio (< 0.18).
        #    Pada foto selfie/manusia: skin_ratio (> 0.25) mendominasi melebihi warna hijau tanaman.
        is_human_dominant = (
            skin_ratio > 0.35
            or (skin_ratio > 0.20 and skin_ratio > green_ratio)
            or (skin_ratio > 0.25 and leaf_ratio < 0.25)
        )
        if is_human_dominant:
            return LeafValidationDecision(
                is_acceptable=False,
                leaf_ratio=leaf_ratio,
                error_code="IMAGE_NOT_LEAF_HUMAN",
                error_message=(
                    "Terdeteksi foto wajah, tangan, atau tubuh manusia, bukan daun padi. "
                    "Arahkan kamera tepat ke daun tanaman padi yang ingin didiagnosa."
                ),
            )

        # 2. Deteksi citra monokrom / kertas / dokumen / semen abu-abu
        if mean_saturation < 12.0 and leaf_ratio < 0.10:
            return LeafValidationDecision(
                is_acceptable=False,
                leaf_ratio=leaf_ratio,
                error_code="IMAGE_NOT_LEAF_MONOCHROME",
                error_message=(
                    "Gambar terdeteksi dokumen, kertas, atau permukaan polos. "
                    "Harap ambil foto daun padi asli di lapangan."
                ),
            )

        # 3. Deteksi warna sintetis tidak wajar (layar HP, poster, tembok cat biru/ungu)
        if unnatural_ratio > 0.45 and leaf_ratio < 0.12:
            return LeafValidationDecision(
                is_acceptable=False,
                leaf_ratio=leaf_ratio,
                error_code="IMAGE_NOT_LEAF",
                error_message=(
                    "Objek pada gambar bukan daun padi. "
                    "Harap arahkan kamera langsung ke daun tanaman padi."
                ),
            )

        # 4. Validasi ambang batas minimum spektrum daun (hijau / lesi / jerami)
        if leaf_ratio < self.min_leaf_ratio:
            # Sedikit lebih toleran jika ada kuning/cokelat yang banyak (daun sangat sakit)
            if leaf_ratio < 0.10:
                return LeafValidationDecision(
                    is_acceptable=False,
                    leaf_ratio=leaf_ratio,
                    error_code="IMAGE_NOT_LEAF",
                    error_message=(
                        "Daun padi tidak terdeteksi pada gambar. "
                        "Pastikan daun padi mengisi sebagian besar frame foto."
                    ),
                )
            else:
                # leaf_ratio 0.10–0.18: berikan warning tapi tetap izinkan
                warnings.append(
                    "Porsi daun pada gambar kecil. "
                    "Hasilnya mungkin kurang akurat — ambil foto lebih dekat ke daun."
                )

        # Peringatan tambahan untuk porsi daun yang cukup kecil
        if leaf_ratio < 0.30 and leaf_ratio >= self.min_leaf_ratio:
            warnings.append(
                "Disarankan mengambil foto lebih dekat agar daun memenuhi frame."
            )

        return LeafValidationDecision(
            is_acceptable=True,
            leaf_ratio=leaf_ratio,
            warnings=warnings,
        )

    def evaluate_model_confidence(
        self, confidence: float, leaf_ratio: float
    ) -> LeafValidationDecision:
        """Memvalidasi keyakinan model terhadap pola penyakit daun padi.

        Jika confidence di bawah threshold minimum, model menganggap gambar
        tidak menunjukkan pola penyakit yang dikenali — lebih baik menolak
        daripada memberikan diagnosis yang menyesatkan.
        """
        if confidence < self.min_disease_confidence:
            return LeafValidationDecision(
                is_acceptable=False,
                leaf_ratio=leaf_ratio,
                error_code="IMAGE_NOT_LEAF_UNRECOGNIZED",
                error_message=(
                    "Pola penyakit tidak teridentifikasi dengan jelas. "
                    "Pastikan foto memperlihatkan bagian daun padi yang mengalami gejala. "
                    "Coba ambil foto lebih dekat dengan pencahayaan cukup."
                ),
            )

        return LeafValidationDecision(
            is_acceptable=True,
            leaf_ratio=leaf_ratio,
        )
