from __future__ import annotations

import json
import logging
from dataclasses import asdict, dataclass
from datetime import UTC, datetime
from pathlib import Path
from typing import Any

import numpy as np

from app.core.constants import SUPPORTED_DISEASE_CODES

logger = logging.getLogger(__name__)


@dataclass
class LearnedLeafSample:
    sample_id: str
    disease_code: str
    disease_name: str
    feature_vector: list[float]
    confidence: float
    source: str  # "farmer_confirmed", "farmer_corrected", "expert_verified"
    learned_at: str


class LeafMemoryBank:
    """Memory bank untuk menyimpan dan memanggil representasi visual daun padi yang pernah dipelajari."""

    TRUSTED_SOURCES = {"farmer_confirmed", "farmer_corrected", "expert_verified"}

    def __init__(self, storage_path: Path | None = None, max_samples_per_class: int = 200) -> None:
        self.storage_path = storage_path
        self.max_samples_per_class = max_samples_per_class
        self._samples: dict[str, LearnedLeafSample] = {}
        if self.storage_path and self.storage_path.exists():
            self.load(self.storage_path)

    @property
    def total_samples(self) -> int:
        return len(self._samples)

    def add_sample(
        self,
        sample_id: str,
        disease_code: str,
        disease_name: str,
        feature_vector: list[float] | np.ndarray,
        confidence: float,
        source: str = "farmer_confirmed",
    ) -> LearnedLeafSample:
        """Menyimpan sampel daun baru ke dalam memory bank."""
        if isinstance(feature_vector, np.ndarray):
            norm = np.linalg.norm(feature_vector)
            normalized_vector = (feature_vector / norm).tolist() if norm > 0 else feature_vector.tolist()
        else:
            arr = np.array(feature_vector, dtype=np.float32)
            norm = np.linalg.norm(arr)
            normalized_vector = (arr / norm).tolist() if norm > 0 else list(feature_vector)

        sample = LearnedLeafSample(
            sample_id=sample_id,
            disease_code=disease_code,
            disease_name=disease_name,
            feature_vector=normalized_vector,
            confidence=round(float(confidence), 4),
            source=source,
            learned_at=datetime.now(UTC).isoformat(),
        )

        self._samples[sample_id] = sample

        if self.storage_path:
            self.save(self.storage_path)

        logger.info(
            "event=leaf_learned sample_id=%s disease=%s source=%s total_memory=%d",
            sample_id,
            disease_code,
            source,
            len(self._samples),
        )
        return sample

    def find_nearest_neighbors(
        self,
        query_vector: np.ndarray | list[float],
        top_k: int = 5,
        min_similarity: float = 0.65,
    ) -> list[tuple[LearnedLeafSample, float]]:
        """Mencari sampel daun masa lalu yang paling mirip menggunakan Cosine Similarity."""
        if not self._samples:
            return []

        q = np.array(query_vector, dtype=np.float32).flatten()
        q_norm = np.linalg.norm(q)
        if q_norm == 0:
            return []
        q = q / q_norm

        results: list[tuple[LearnedLeafSample, float]] = []
        skipped_dimension_mismatch = 0
        for sample in self._samples.values():
            ref = np.array(sample.feature_vector, dtype=np.float32).flatten()
            if ref.shape != q.shape:
                skipped_dimension_mismatch += 1
                continue
            ref_norm = np.linalg.norm(ref)
            if ref_norm == 0:
                continue
            ref = ref / ref_norm

            similarity = float(np.dot(q, ref))
            if similarity >= min_similarity:
                results.append((sample, similarity))

        if skipped_dimension_mismatch:
            logger.warning(
                "event=memory_bank_dimension_mismatch query_dim=%d skipped=%d total_memory=%d",
                q.size,
                skipped_dimension_mismatch,
                len(self._samples),
            )

        results.sort(key=lambda item: item[1], reverse=True)
        return results[:top_k]

    def compute_memory_refinement(
        self,
        query_vector: np.ndarray | list[float],
        base_disease_code: str,
        base_confidence: float,
        top_k: int = 5,
    ) -> tuple[str, str, float, bool]:
        """
        Menyempurnakan prediksi model berdasarkan daun-daun masa lalu yang telah dipelajari.
        Mengembalikan: (disease_code, disease_name, refined_confidence, is_memory_boosted)
        """
        neighbors = self.find_nearest_neighbors(query_vector, top_k=top_k)
        if not neighbors:
            return base_disease_code, "", base_confidence, False

        # Hitung voting berbobot (weighted similarity score per class)
        class_weights: dict[str, float] = {}
        class_names: dict[str, str] = {}
        total_weight = 0.0

        for sample, sim in neighbors:
            # Berikan bobot lebih tinggi untuk sampel yang diverifikasi ahli/petani.
            source_multiplier = (
                1.3 if sample.source in ("expert_verified", "farmer_confirmed", "farmer_corrected") else 1.0
            )
            weight = (sim ** 2) * sample.confidence * source_multiplier
            class_weights[sample.disease_code] = class_weights.get(sample.disease_code, 0.0) + weight
            class_names[sample.disease_code] = sample.disease_name
            total_weight += weight

        if total_weight == 0:
            return base_disease_code, "", base_confidence, False

        # Cari kelas dengan kemiripan memori tertinggi
        best_memory_class = max(class_weights.keys(), key=lambda c: class_weights[c])
        memory_support = class_weights[best_memory_class] / total_weight

        # Jika memori sangat mendukung kelas dasar yang sama, tingkatkan confidence
        if best_memory_class == base_disease_code:
            boosted_confidence = min(0.9999, base_confidence + (0.05 * memory_support))
            return base_disease_code, class_names.get(base_disease_code, ""), boosted_confidence, True

        # Jika tetangga terdekat sangat mirip (> 0.88) dan diverifikasi, koreksi ke kelas memori
        strongest_sim = neighbors[0][1]
        if strongest_sim >= 0.88 and neighbors[0][0].source in (
            "expert_verified",
            "farmer_confirmed",
            "farmer_corrected",
        ):
            refined_confidence = max(base_confidence, neighbors[0][0].confidence * strongest_sim)
            logger.info(
                "event=memory_corrected base=%s refined=%s sim=%.3f",
                base_disease_code,
                best_memory_class,
                strongest_sim,
            )
            return best_memory_class, class_names.get(best_memory_class, ""), refined_confidence, True

        return base_disease_code, "", base_confidence, False

    def get_stats(self) -> dict[str, Any]:
        """Mendapatkan statistik daun yang telah dipelajari."""
        distribution: dict[str, int] = {}
        sources: dict[str, int] = {}
        for sample in self._samples.values():
            distribution[sample.disease_code] = distribution.get(sample.disease_code, 0) + 1
            sources[sample.source] = sources.get(sample.source, 0) + 1

        return {
            "total_learned_leaves": len(self._samples),
            "disease_distribution": distribution,
            "source_distribution": sources,
            "storage_path": str(self.storage_path) if self.storage_path else None,
        }

    def save(self, target_path: Path) -> None:
        """Menyimpan seluruh memori ke disk."""
        target_path.parent.mkdir(parents=True, exist_ok=True)
        data = {sample_id: asdict(sample) for sample_id, sample in self._samples.items()}
        target_path.write_text(json.dumps(data, indent=2), encoding="utf-8")

    def load(self, source_path: Path) -> None:
        """Memuat memori dari disk."""
        try:
            raw = json.loads(source_path.read_text(encoding="utf-8"))
            skipped = 0
            for sample_id, item in raw.items():
                disease_code = str(item.get("disease_code", "")).strip().lower()
                source = str(item.get("source", "")).strip().lower()
                if (
                    disease_code not in SUPPORTED_DISEASE_CODES
                    or disease_code == "unknown"
                    or source not in self.TRUSTED_SOURCES
                ):
                    skipped += 1
                    continue
                item["disease_code"] = disease_code
                item["source"] = source
                self._samples[sample_id] = LearnedLeafSample(**item)
            logger.info("event=memory_bank_loaded count=%d skipped=%d", len(self._samples), skipped)
        except Exception as exc:
            logger.warning("event=memory_bank_load_failed error=%s", exc)
