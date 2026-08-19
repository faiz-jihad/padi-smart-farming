from __future__ import annotations

import argparse
from datetime import date

import httpx


def main() -> None:
    parser = argparse.ArgumentParser(description="Smoke test P.A.D.I. AI service endpoints.")
    parser.add_argument("--base-url", default="http://127.0.0.1:8000")
    parser.add_argument("--image-path", default="")
    args = parser.parse_args()

    base_url = args.base_url.rstrip("/")
    with httpx.Client(timeout=30) as client:
        health_response = client.get(f"{base_url}/api/v1/health")
        health_response.raise_for_status()
        print("health:", health_response.json())

        treatment_response = client.post(
            f"{base_url}/api/v1/treatments/recommend",
            json={
                "disease_code": "blast",
                "confidence": 0.91,
                "plant_age_days": 45,
                "severity": "medium",
                "affected_area_percentage": 12,
                "weather_condition": "humid",
                "actions_already_taken": [],
            },
        )
        treatment_response.raise_for_status()
        print("treatment:", treatment_response.json())

        planting_response = client.post(
            f"{base_url}/api/v1/planting/recommend",
            json={
                "latitude": -6.3266,
                "longitude": 108.32,
                "rice_variety": "Ciherang",
                "irrigation_type": "technical",
                "land_area_hectares": 1.0,
                "preferred_start_date": date.today().isoformat(),
            },
        )
        planting_response.raise_for_status()
        print("planting:", planting_response.json())

        if args.image_path:
            with open(args.image_path, "rb") as image_file:
                detection_response = client.post(
                    f"{base_url}/api/v1/diseases/detect",
                    files={"image": ("sample.jpg", image_file, "image/jpeg")},
                )
            detection_response.raise_for_status()
            print("detection:", detection_response.json())


if __name__ == "__main__":
    main()
