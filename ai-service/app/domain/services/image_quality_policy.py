from __future__ import annotations

from dataclasses import dataclass


@dataclass(frozen=True)
class ImageQualityDecision:
    is_acceptable: bool
    warnings: list[str]
    error_code: str | None = None
    error_message: str | None = None


class ImageQualityPolicy:
    def __init__(self, min_blur_score: float, min_brightness: float, max_brightness: float) -> None:
        self.min_blur_score = min_blur_score
        self.min_brightness = min_brightness
        self.max_brightness = max_brightness

    def evaluate(self, blur_score: float, brightness_score: float) -> ImageQualityDecision:
        """Mengevaluasi kelayakan kualitas foto untuk inferensi."""
        if blur_score < self.min_blur_score:
            return ImageQualityDecision(
                is_acceptable=False,
                warnings=["Foto terlalu buram."],
                error_code="IMAGE_TOO_BLURRY",
                error_message="Foto terlalu buram. Dekatkan kamera dan ambil ulang foto.",
            )
        if brightness_score < self.min_brightness:
            return ImageQualityDecision(
                is_acceptable=False,
                warnings=["Foto terlalu gelap."],
                error_code="IMAGE_TOO_DARK",
                error_message="Foto terlalu gelap. Ambil foto di tempat yang lebih terang.",
            )
        if brightness_score > self.max_brightness:
            return ImageQualityDecision(
                is_acceptable=False,
                warnings=["Foto terlalu terang."],
                error_code="IMAGE_TOO_BRIGHT",
                error_message="Foto terlalu terang. Hindari cahaya langsung dan ambil ulang foto.",
            )
        return ImageQualityDecision(is_acceptable=True, warnings=[])
