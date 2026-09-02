from __future__ import annotations

from pydantic import BaseModel, Field


# ── Sub-schemas ───────────────────────────────────────────────────────────────


class ModelMeta(BaseModel):
    name: str
    version: str
    task: str
    imgsz: int


class TopPredictionItem(BaseModel):
    rank: int
    class_name: str
    confidence: float


class SecondPrediction(BaseModel):
    class_name: str
    confidence: float


class PredictionResult(BaseModel):
    class_id: int
    class_name: str
    display_name: str
    confidence: float
    confidence_percent: float
    second_prediction: SecondPrediction | None
    margin: float
    top_predictions: list[TopPredictionItem]


class DecisionResult(BaseModel):
    status: str  # HIGH_CONFIDENCE | NEED_PPL_REVIEW | UNCERTAIN | RETAKE_PHOTO
    needs_ppl_review: bool
    needs_retake: bool
    needs_differential_review: bool


class QualityResult(BaseModel):
    status: str  # GOOD | LOW_QUALITY | INVALID
    blur_score: float
    brightness: float
    width: int
    height: int
    issues: list[str]


class DiseaseInfo(BaseModel):
    name: str
    class_name: str
    description: str
    symptoms: list[str]
    recommended_actions: list[str]
    severity_note: str


# ── Main diagnosis response ───────────────────────────────────────────────────


class DiagnosisData(BaseModel):
    request_id: str
    diagnosis_id: str
    model: ModelMeta
    prediction: PredictionResult
    decision: DecisionResult
    quality: QualityResult
    disease: DiseaseInfo
    latency_ms: float


class DiagnosisResponse(BaseModel):
    success: bool = True
    data: DiagnosisData


# ── Model info ────────────────────────────────────────────────────────────────


class ModelInfoResponse(BaseModel):
    name: str
    version: str
    full_name: str
    task: str
    classes: list[str]
    num_classes: int
    imgsz: int
    device: str
    loaded: bool


# ── Health ────────────────────────────────────────────────────────────────────


class HealthSimpleResponse(BaseModel):
    status: str = "ok"


class HealthDetailResponse(BaseModel):
    status: str
    model_loaded: bool
    device: str
    model_version: str
    model_name: str


# ── Feedback ──────────────────────────────────────────────────────────────────


class FeedbackRequest(BaseModel):
    diagnosis_id: str = Field(..., description="UUID dari diagnosis yang di-feedback")
    ai_prediction: str = Field(..., description="Nama kelas prediksi AI")
    verified_label: str = Field(..., description="Label yang diverifikasi PPL/ahli")
    verified_by: str = Field(default="", description="ID atau nama verifikator")
    verification_source: str = Field(
        default="PPL",
        description="Sumber verifikasi: PPL | AGRONOMIST | LAB | SELF",
    )
    notes: str | None = Field(default=None, description="Catatan tambahan dari verifikator")


class FeedbackResponse(BaseModel):
    success: bool = True
    feedback_id: str
    message: str
