from app.domain.services.image_quality_policy import ImageQualityPolicy


def test_image_quality_policy_rejects_blurry_image():
    policy = ImageQualityPolicy(min_blur_score=100, min_brightness=40, max_brightness=220)

    decision = policy.evaluate(blur_score=10, brightness_score=120)

    assert decision.is_acceptable is False
    assert decision.error_code == "IMAGE_TOO_BLURRY"


def test_image_quality_policy_accepts_good_image():
    policy = ImageQualityPolicy(min_blur_score=100, min_brightness=40, max_brightness=220)

    decision = policy.evaluate(blur_score=140, brightness_score=120)

    assert decision.is_acceptable is True
    assert decision.warnings == []
