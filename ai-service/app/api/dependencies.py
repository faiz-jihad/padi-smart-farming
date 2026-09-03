from __future__ import annotations

from pathlib import Path

from fastapi import Request

from app.application.use_cases.detect_disease import DetectDiseaseUseCase
from app.application.use_cases.generate_treatment import GenerateTreatmentUseCase
from app.application.use_cases.learn_disease_scan import LearnDiseaseScanUseCase
from app.application.use_cases.recommend_planting_time import RecommendPlantingTimeUseCase
from app.core.config import Settings
from app.domain.services.confidence_policy import ConfidencePolicy
from app.domain.services.image_quality_policy import ImageQualityPolicy
from app.domain.services.leaf_memory_bank import LeafMemoryBank
from app.domain.services.leaf_validation_policy import LeafValidationPolicy
from app.domain.services.planting_scoring_policy import PlantingScoringPolicy
from app.infrastructure.llm.llm_client import LlmClient
from app.infrastructure.llm.treatment_generator import TreatmentGenerator
from app.infrastructure.machine_learning.disease_classifier import DiseaseClassifier
from app.infrastructure.machine_learning.image_preprocessor import ImagePreprocessor
from app.infrastructure.machine_learning.label_mapper import LabelMapper
from app.infrastructure.machine_learning.yolo_disease_detector import YoloDiseaseDetector
from app.infrastructure.persistence.knowledge_base_repository import KnowledgeBaseRepository
from app.infrastructure.weather.weather_client import WeatherClient
from app.infrastructure.weather.weather_repository_impl import WeatherRepositoryImpl


class ServiceContainer:
    def __init__(self, settings: Settings) -> None:
        self.settings = settings
        self.image_preprocessor = ImagePreprocessor()
        label_mapper = LabelMapper(settings.model_class_mapping)
        if settings.model_path.suffix.lower() == ".pt":
            self.disease_classifier = YoloDiseaseDetector(
                model_path=settings.model_path,
                model_version=settings.model_version,
                label_mapper=label_mapper,
            )
        else:
            self.disease_classifier = DiseaseClassifier(
                model_path=settings.model_path,
                model_version=settings.model_version,
                image_preprocessor=self.image_preprocessor,
                label_mapper=label_mapper,
            )
        self.confidence_policy = ConfidencePolicy(
            high_threshold=settings.model_confidence_high,
            medium_threshold=settings.model_confidence_medium,
        )
        self.image_quality_policy = ImageQualityPolicy(
            min_blur_score=settings.min_blur_score,
            min_brightness=settings.min_brightness,
            max_brightness=settings.max_brightness,
        )
        self.leaf_validation_policy = LeafValidationPolicy(
            min_leaf_ratio=settings.min_leaf_ratio,
            min_disease_confidence=settings.min_disease_confidence,
        )
        base_dir = Path(__file__).resolve().parents[2]
        self.leaf_memory_bank = LeafMemoryBank(
            storage_path=base_dir / "models" / "leaf_memory_bank.json"
        )
        self.knowledge_base_repository = KnowledgeBaseRepository(
            guidelines_path=base_dir / "knowledge_base" / "treatment_guidelines.json"
        )
        self.llm_client = LlmClient(
            api_key=settings.llm_api_key,
            model=settings.llm_model,
            base_url=settings.llm_base_url,
            timeout_seconds=settings.llm_timeout_seconds,
        )
        self.weather_repository = WeatherRepositoryImpl(
            WeatherClient(
                base_url=settings.weather_base_url,
                api_key=settings.weather_api_key,
                timeout_seconds=settings.weather_timeout_seconds,
            )
        )

    def load_startup_resources(self) -> None:
        self.disease_classifier.load()


def get_container(request: Request) -> ServiceContainer:
    return request.app.state.container


def get_detect_disease_use_case(request: Request) -> DetectDiseaseUseCase:
    container = get_container(request)
    return DetectDiseaseUseCase(
        model_repository=container.disease_classifier,
        image_preprocessor=container.image_preprocessor,
        confidence_policy=container.confidence_policy,
        image_quality_policy=container.image_quality_policy,
        max_image_size_bytes=container.settings.max_image_size_bytes,
        leaf_validation_policy=container.leaf_validation_policy,
        leaf_memory_bank=container.leaf_memory_bank,
        model_accuracy=container.settings.model_reported_accuracy,
    )


def get_learn_disease_scan_use_case(request: Request) -> LearnDiseaseScanUseCase:
    container = get_container(request)
    return LearnDiseaseScanUseCase(
        image_preprocessor=container.image_preprocessor,
        disease_classifier=container.disease_classifier,
        leaf_memory_bank=container.leaf_memory_bank,
        leaf_validation_policy=container.leaf_validation_policy,
    )




def get_generate_treatment_use_case(request: Request) -> GenerateTreatmentUseCase:
    container = get_container(request)
    return GenerateTreatmentUseCase(
        knowledge_base_repository=container.knowledge_base_repository,
        treatment_generator=TreatmentGenerator(container.llm_client),
    )


def get_recommend_planting_time_use_case(request: Request) -> RecommendPlantingTimeUseCase:
    container = get_container(request)
    return RecommendPlantingTimeUseCase(
        weather_repository=container.weather_repository,
        scoring_policy=PlantingScoringPolicy(),
    )
