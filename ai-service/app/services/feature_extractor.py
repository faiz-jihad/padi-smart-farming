"""
Feature Extraction Service for P.A.D.I.
Arsitektur Tahap 3: [Citra Input] -> [Segmentasi] -> [Ekstraksi Fitur] -> [Klasifikasi]
"""

import time
import logging
from dataclasses import dataclass, asdict
from typing import Any, Dict, List
import cv2
import numpy as np
from PIL import Image
from app.services.leaf_segmenter import SegmentationResult

logger = logging.getLogger(__name__)

@dataclass
class ExtractedFeatures:
    color_features: Dict[str, Any]
    texture_features: Dict[str, Any]
    morphology_features: Dict[str, Any]
    processing_ms: float

    def to_dict(self) -> Dict[str, Any]:
        return asdict(self)


class FeatureExtractor:
    """
    Layanan Ekstraksi Fitur Citra Daun Padi (Warna, Tekstur, dan Morfologi).
    """

    def extract(self, image: Image.Image, seg_result: SegmentationResult) -> ExtractedFeatures:
        t0 = time.perf_counter()
        rgb = np.array(image, dtype=np.uint8)
        rgb_f = rgb.astype(np.float32)

        r = rgb_f[:, :, 0]
        g = rgb_f[:, :, 1]
        b = rgb_f[:, :, 2]

        # 1. Fitur Warna
        mean_r = round(float(np.mean(r)), 2)
        mean_g = round(float(np.mean(g)), 2)
        mean_b = round(float(np.mean(b)), 2)

        hsv = cv2.cvtColor(rgb, cv2.COLOR_RGB2HSV).astype(np.float32)
        mean_h = round(float(np.mean(hsv[:, :, 0])), 2)
        mean_s = round(float(np.mean(hsv[:, :, 1])), 2)
        mean_v = round(float(np.mean(hsv[:, :, 2])), 2)

        # Excess Green Index: 2G - R - B
        exg = 2.0 * g - r - b
        mean_exg = round(float(np.mean(exg)), 2)

        # Chlorosis Index (Tingkat Klorosis/Menguning): (R + G) / (2B + 1)
        chlorosis_idx = round(float(np.mean((r + g) / (2.0 * b + 1.0))), 2)

        # Necrosis Ratio: Proporsi piksel coklat/kering nekrotik
        necrosis_ratio = round(float(seg_result.lesion_area_pct / 100.0), 3)

        color_features = {
            "mean_rgb": [mean_r, mean_g, mean_b],
            "mean_hsv": [mean_h, mean_s, mean_v],
            "greenness_exg": mean_exg,
            "chlorosis_index": chlorosis_idx,
            "necrosis_ratio": necrosis_ratio,
        }

        # 2. Fitur Tekstur (Texture Roughness via Laplacian & Sobel Gradient)
        gray = cv2.cvtColor(rgb, cv2.COLOR_RGB2GRAY)
        laplacian_var = round(float(cv2.Laplacian(gray, cv2.CV_64F).var()), 2)

        sobelx = cv2.Sobel(gray, cv2.CV_64F, 1, 0, ksize=3)
        sobely = cv2.Sobel(gray, cv2.CV_64F, 0, 1, ksize=3)
        gradient_mag = np.sqrt(sobelx**2 + sobely**2)
        mean_gradient = round(float(np.mean(gradient_mag)), 2)

        # Homogeneity Score (0.0 kasar/bercak parah, 1.0 daun rata mulus)
        homogeneity = round(float(1.0 / (1.0 + (mean_gradient / 50.0))), 3)

        texture_features = {
            "roughness_laplacian": laplacian_var,
            "edge_gradient_mean": mean_gradient,
            "homogeneity_score": homogeneity,
        }

        # 3. Fitur Morfologi Lesi
        bbox = seg_result.dominant_bbox
        aspect_ratio = 1.0
        if bbox and bbox[3] > 0:
            aspect_ratio = round(float(bbox[2]) / float(bbox[3]), 2)

        morphology_features = {
            "spot_count": seg_result.spot_count,
            "lesion_density_pct": seg_result.lesion_area_pct,
            "dominant_lesion_aspect_ratio": aspect_ratio,
            "severity_level": seg_result.severity_level,
        }

        elapsed_ms = round((time.perf_counter() - t0) * 1000.0, 2)
        return ExtractedFeatures(
            color_features=color_features,
            texture_features=texture_features,
            morphology_features=morphology_features,
            processing_ms=elapsed_ms,
        )
