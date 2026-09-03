"""
Leaf and Lesion Segmentation Service for P.A.D.I.
Arsitektur Tahap 2: [Citra Input] -> [Segmentasi] -> [Ekstraksi Fitur] -> [Klasifikasi]
"""

import time
import logging
from dataclasses import dataclass, asdict
from typing import Any, Dict, List, Optional
import cv2
import numpy as np
from PIL import Image

logger = logging.getLogger(__name__)

@dataclass
class SegmentationResult:
    leaf_detected: bool
    leaf_coverage_pct: float
    lesion_area_pct: float
    spot_count: int
    dominant_bbox: Optional[List[int]]
    severity_level: str
    processing_ms: float

    def to_dict(self) -> Dict[str, Any]:
        return asdict(self)


class LeafSegmenter:
    """
    Layanan Segmentasi Citra Daun Padi dan Lesi Penyakit.
    """

    def __init__(self) -> None:
        self._kernel_ellipse = cv2.getStructuringElement(cv2.MORPH_ELLIPSE, (5, 5))
        self._kernel_spot = cv2.getStructuringElement(cv2.MORPH_ELLIPSE, (3, 3))

    def segment(self, image: Image.Image) -> SegmentationResult:
        t0 = time.perf_counter()
        rgb = np.array(image, dtype=np.float32)
        h, w = rgb.shape[:2]
        total_pixels = h * w

        r, g, b = rgb[:, :, 0], rgb[:, :, 1], rgb[:, :, 2]
        exg = 2.0 * g - r - b

        # 1. Segmentasi Daun Padi
        leaf_condition = (g > 35.0) & (exg > -25.0) & (g > b * 0.88)
        leaf_mask = leaf_condition.astype(np.uint8) * 255
        leaf_mask = cv2.morphologyEx(leaf_mask, cv2.MORPH_CLOSE, self._kernel_ellipse)

        leaf_pixel_count = int(np.count_nonzero(leaf_mask))
        leaf_coverage_pct = round((leaf_pixel_count / max(total_pixels, 1)) * 100.0, 2)
        leaf_detected = leaf_coverage_pct >= 5.0

        if not leaf_detected:
            elapsed_ms = round((time.perf_counter() - t0) * 1000.0, 2)
            return SegmentationResult(
                leaf_detected=False,
                leaf_coverage_pct=leaf_coverage_pct,
                lesion_area_pct=0.0,
                spot_count=0,
                dominant_bbox=None,
                severity_level="none",
                processing_ms=elapsed_ms,
            )

        # 2. Segmentasi Lesi Bercak
        lesion_condition = (leaf_mask > 0) & ((exg < 12.0) | (r > g * 0.88))
        lesion_mask = lesion_condition.astype(np.uint8) * 255
        lesion_mask = cv2.morphologyEx(lesion_mask, cv2.MORPH_OPEN, self._kernel_spot)

        lesion_pixel_count = int(np.count_nonzero(lesion_mask))
        lesion_area_pct = round((lesion_pixel_count / max(leaf_pixel_count, 1)) * 100.0, 2)

        # 3. Kontur Bercak & Bounding Box
        contours, _ = cv2.findContours(lesion_mask, cv2.RETR_EXTERNAL, cv2.CHAIN_APPROX_SIMPLE)
        min_spot_area = max(10, int(total_pixels * 0.0001))
        significant_spots = [c for c in contours if cv2.contourArea(c) >= min_spot_area]
        spot_count = len(significant_spots)

        dominant_bbox = None
        if significant_spots:
            largest_contour = max(significant_spots, key=cv2.contourArea)
            x, y, bw, bh = cv2.boundingRect(largest_contour)
            dominant_bbox = [int(x), int(y), int(bw), int(bh)]

        # 4. Tingkat Keparahan
        if lesion_area_pct >= 60.0:
            severity = "berat"
        elif lesion_area_pct >= 25.0:
            severity = "sedang"
        elif lesion_area_pct >= 5.0:
            severity = "ringan"
        else:
            severity = "normal"

        elapsed_ms = round((time.perf_counter() - t0) * 1000.0, 2)
        return SegmentationResult(
            leaf_detected=True,
            leaf_coverage_pct=leaf_coverage_pct,
            lesion_area_pct=lesion_area_pct,
            spot_count=spot_count,
            dominant_bbox=dominant_bbox,
            severity_level=severity,
            processing_ms=elapsed_ms,
        )
