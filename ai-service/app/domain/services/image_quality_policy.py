from __future__ import annotations

from dataclasses import dataclass


@dataclass(frozen=True)
class ImageQualityDecision:
    is_acceptable: bool
    warnings: list[str]
    error_code: str | None = None
    error_message: str | None = None


class ImageQualityPolicy:
    """Kebijakan evaluasi kualitas foto sebelum masuk ke inferensi model.

    Parameter:
    - min_blur_score: variance of Laplacian — semakin tinggi semakin tajam.
      Nilai 80 ditentukan berdasarkan kondisi lapangan (foto handheld di sawah).
    - min_brightness / max_brightness: rata-rata grayscale 0–255.
    - min_resolution: dimensi minimum (lebar atau tinggi) dalam piksel.
    """

    def __init__(
        self,
        min_blur_score: float,
        min_brightness: float,
        max_brightness: float,
        min_resolution: int = 100,
    ) -> None:
        self.min_blur_score = min_blur_score
        self.min_brightness = min_brightness
        self.max_brightness = max_brightness
        self.min_resolution = min_resolution

    def evaluate(
        self,
        blur_score: float,
        brightness_score: float,
        image_height: int = 0,
        image_width: int = 0,
    ) -> ImageQualityDecision:
        """Mengevaluasi kelayakan kualitas foto untuk inferensi.

        Urutan pengecekan:
        1. Resolusi minimum — terlalu kecil berarti tidak ada informasi tekstur
        2. Blur — gambar buram menghilangkan detail morfologi lesi
        3. Kecerahan — terlalu gelap / terlalu terang mengaburkan warna lesi
        """
        # 1. Cek resolusi minimum (jika dimensi tersedia)
        if image_height > 0 and image_width > 0:
            min_dim = min(image_height, image_width)
            if min_dim < self.min_resolution:
                return ImageQualityDecision(
                    is_acceptable=False,
                    warnings=[f"Resolusi gambar terlalu kecil ({image_width}×{image_height}px)."],
                    error_code="IMAGE_TOO_SMALL",
                    error_message=(
                        f"Resolusi gambar terlalu kecil ({image_width}×{image_height}px). "
                        f"Gunakan gambar minimal {self.min_resolution}×{self.min_resolution} piksel."
                    ),
                )

        # 2. Cek blur
        if blur_score < self.min_blur_score:
            return ImageQualityDecision(
                is_acceptable=False,
                warnings=["Foto terlalu buram."],
                error_code="IMAGE_TOO_BLURRY",
                error_message=(
                    "Foto terlalu buram. Dekatkan kamera ke daun dan pastikan gambar fokus."
                ),
            )

        # 3. Cek kecerahan
        if brightness_score < self.min_brightness:
            return ImageQualityDecision(
                is_acceptable=False,
                warnings=["Foto terlalu gelap."],
                error_code="IMAGE_TOO_DARK",
                error_message=(
                    "Foto terlalu gelap. Ambil foto di tempat yang lebih terang "
                    "atau aktifkan flash kamera."
                ),
            )
        if brightness_score > self.max_brightness:
            return ImageQualityDecision(
                is_acceptable=False,
                warnings=["Foto terlalu terang."],
                error_code="IMAGE_TOO_BRIGHT",
                error_message=(
                    "Foto terlalu terang atau terpapar cahaya langsung. "
                    "Hindari sinar matahari langsung dan ambil ulang foto."
                ),
            )

        return ImageQualityDecision(is_acceptable=True, warnings=[])
