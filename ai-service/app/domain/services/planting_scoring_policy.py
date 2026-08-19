from __future__ import annotations

from datetime import date, timedelta

from app.domain.entities.planting_recommendation import PlantingRecommendation, WeatherForecastDay


class PlantingScoringPolicy:
    def recommend(
        self,
        forecast_days: list[WeatherForecastDay],
        preferred_start_date: date,
        weather_source: str,
    ) -> PlantingRecommendation:
        """Menghitung rekomendasi tanam rule-based untuk MVP."""
        if not forecast_days:
            raise ValueError("Data prakiraan cuaca tidak tersedia")

        avg_rainfall = sum(day.rainfall_mm for day in forecast_days) / len(forecast_days)
        avg_temperature = sum(day.temperature_c for day in forecast_days) / len(forecast_days)
        avg_humidity = sum(day.humidity_percent for day in forecast_days) / len(forecast_days)

        score = 50
        reasons: list[str] = []

        if 5 <= avg_rainfall <= 30:
            score += 25
            reasons.append("Curah hujan rata-rata berada pada rentang yang mendukung awal tanam.")
        elif avg_rainfall > 50:
            score -= 25
            reasons.append("Curah hujan tinggi meningkatkan risiko genangan dan penyakit.")
        else:
            score -= 10
            reasons.append("Curah hujan rendah, pastikan ketersediaan air mencukupi.")

        if 24 <= avg_temperature <= 32:
            score += 15
            reasons.append("Suhu rata-rata masih sesuai untuk pertumbuhan padi.")
        else:
            score -= 10
            reasons.append("Suhu rata-rata kurang ideal untuk awal musim tanam.")

        if 60 <= avg_humidity <= 85:
            score += 10
        elif avg_humidity > 90:
            score -= 10
            reasons.append("Kelembapan tinggi dapat meningkatkan risiko penyakit daun.")

        bounded_score = max(0, min(100, score))
        if bounded_score >= 75:
            risk_level = "low"
            start_offset = 0
        elif bounded_score >= 55:
            risk_level = "medium"
            start_offset = 3
            reasons.append("Kondisi cukup layak, namun pemantauan cuaca harian tetap diperlukan.")
        else:
            risk_level = "high"
            start_offset = 7
            reasons.append("Sebaiknya tunda tanam sampai kondisi cuaca lebih stabil.")

        start_date = preferred_start_date + timedelta(days=start_offset)
        return PlantingRecommendation(
            suitability_score=bounded_score,
            risk_level=risk_level,
            recommended_start_date=start_date,
            recommended_end_date=start_date + timedelta(days=7),
            reasons=reasons,
            weather_source=weather_source,
        )
