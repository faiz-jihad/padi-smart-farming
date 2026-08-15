from __future__ import annotations

from app.application.dto.planting_recommendation_dto import PlantingRecommendationInput
from app.domain.entities.planting_recommendation import PlantingRecommendation
from app.domain.repositories.weather_repository import WeatherRepository
from app.domain.services.planting_scoring_policy import PlantingScoringPolicy


class RecommendPlantingTimeUseCase:
    def __init__(self, weather_repository: WeatherRepository, scoring_policy: PlantingScoringPolicy) -> None:
        self.weather_repository = weather_repository
        self.scoring_policy = scoring_policy

    def execute(self, recommendation_input: PlantingRecommendationInput) -> PlantingRecommendation:
        """Membuat rekomendasi waktu tanam dari prakiraan cuaca."""
        forecast_days, weather_source = self.weather_repository.get_forecast(
            recommendation_input.latitude,
            recommendation_input.longitude,
        )
        return self.scoring_policy.recommend(
            forecast_days=forecast_days,
            preferred_start_date=recommendation_input.preferred_start_date,
            weather_source=weather_source,
        )
