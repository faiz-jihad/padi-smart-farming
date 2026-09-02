from __future__ import annotations

import logging
from dataclasses import dataclass

from app.services.image_quality import QualityReport
from app.services.padi_classifier import ClassifierResult

logger = logging.getLogger(__name__)

# Known confusion pairs dari validasi model
# Format: frozenset({class_a, class_b})
KNOWN_CONFUSION_PAIRS: list[frozenset[str]] = [
    frozenset({"normal", "hispa"}),
    frozenset({"blast", "hispa"}),
    frozenset({"downy_mildew", "tungro"}),
    frozenset({"downy_mildew", "blast"}),
    frozenset({"brown_spot", "blast"}),
]


@dataclass
class DecisionResult:
    status: str  # HIGH_CONFIDENCE | NEED_PPL_REVIEW | UNCERTAIN | RETAKE_PHOTO
    needs_ppl_review: bool
    needs_retake: bool
    needs_differential_review: bool
    status_reason: str  # Internal log


class DecisionEngine:
    """
    Decision engine berbasis confidence, margin, dan kualitas gambar.

    Prinsip:
    - Confidence tinggi bukan berarti diagnosis pasti
    - Confusion pairs dideteksi untuk mendorong PPL review
    - Status dipakai sebagai sinyal UX, bukan kepastian klinis

    Mapping status -> UI yang disarankan:
    HIGH_CONFIDENCE    -> "AI cukup yakin"
    NEED_PPL_REVIEW    -> "Disarankan verifikasi penyuluh"
    UNCERTAIN          -> "AI belum cukup yakin"
    RETAKE_PHOTO       -> "Ambil foto ulang"
    """

    def __init__(
        self,
        high_confidence_threshold: float = 0.85,
        review_confidence_threshold: float = 0.60,
        min_margin_threshold: float = 0.20,
    ) -> None:
        self._high_conf = high_confidence_threshold
        self._review_conf = review_confidence_threshold
        self._min_margin = min_margin_threshold

    def decide(
        self,
        classifier_result: ClassifierResult,
        quality_report: QualityReport,
    ) -> DecisionResult:
        """
        Evaluasi classifier result + quality report dan buat decision.

        Decision flow:
        A. Kualitas sangat buruk (INVALID) → RETAKE_PHOTO
        B. confidence >= high & margin >= min_margin & quality == GOOD → HIGH_CONFIDENCE
           Namun jika ada confusion pair → NEED_PPL_REVIEW
        C. confidence >= review_threshold → NEED_PPL_REVIEW
        D. confidence < review_threshold → UNCERTAIN
        """
        conf = classifier_result.confidence
        margin = classifier_result.margin
        top1_class = classifier_result.class_name
        top2_class = (
            classifier_result.top_predictions[1].class_name
            if len(classifier_result.top_predictions) > 1
            else None
        )

        # A. Kualitas sangat buruk
        if quality_report.status == "INVALID":
            return DecisionResult(
                status="RETAKE_PHOTO",
                needs_ppl_review=False,
                needs_retake=True,
                needs_differential_review=False,
                status_reason=f"Quality INVALID: issues={quality_report.issues}",
            )

        # Cek confusion pair
        is_confusion_pair = self._is_known_confusion(top1_class, top2_class)
        # Cek margin kecil pada confusion pair
        confusion_with_tight_margin = is_confusion_pair and margin < self._min_margin

        # B. High confidence path
        if (
            conf >= self._high_conf
            and margin >= self._min_margin
            and quality_report.status == "GOOD"
            and not is_confusion_pair
        ):
            return DecisionResult(
                status="HIGH_CONFIDENCE",
                needs_ppl_review=False,
                needs_retake=False,
                needs_differential_review=False,
                status_reason=(
                    f"conf={conf:.3f} margin={margin:.3f} quality=GOOD no_confusion"
                ),
            )

        # B'. High confidence tapi ada confusion pair → paksa PPL review
        if conf >= self._high_conf and is_confusion_pair:
            return DecisionResult(
                status="NEED_PPL_REVIEW",
                needs_ppl_review=True,
                needs_retake=False,
                needs_differential_review=True,
                status_reason=(
                    f"conf={conf:.3f} tapi confusion_pair=({top1_class},{top2_class})"
                ),
            )

        # B''. High confidence tapi margin kecil
        if conf >= self._high_conf and margin < self._min_margin:
            return DecisionResult(
                status="NEED_PPL_REVIEW",
                needs_ppl_review=True,
                needs_retake=False,
                needs_differential_review=is_confusion_pair,
                status_reason=(
                    f"conf={conf:.3f} tapi margin={margin:.3f} < {self._min_margin}"
                ),
            )

        # C. Medium confidence → PPL review
        if conf >= self._review_conf:
            return DecisionResult(
                status="NEED_PPL_REVIEW",
                needs_ppl_review=True,
                needs_retake=False,
                needs_differential_review=is_confusion_pair,
                status_reason=f"conf={conf:.3f} in review range",
            )

        # D. Low confidence → uncertain, sarankan retake
        return DecisionResult(
            status="UNCERTAIN",
            needs_ppl_review=True,
            needs_retake=True,
            needs_differential_review=False,
            status_reason=f"conf={conf:.3f} < review_threshold={self._review_conf}",
        )

    def _is_known_confusion(
        self,
        top1: str,
        top2: str | None,
    ) -> bool:
        if top2 is None:
            return False
        pair = frozenset({top1, top2})
        return pair in KNOWN_CONFUSION_PAIRS
