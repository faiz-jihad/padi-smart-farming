from app.domain.services.confidence_policy import ConfidencePolicy


def test_confidence_policy_high_medium_low_levels():
    policy = ConfidencePolicy(high_threshold=0.85, medium_threshold=0.70)

    assert policy.evaluate(0.90).level == "high"
    assert policy.evaluate(0.80).level == "medium"
    low_decision = policy.evaluate(0.60)

    assert low_decision.level == "low"
    assert low_decision.needs_expert_review is True
