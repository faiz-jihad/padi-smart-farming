"""
Test script untuk menguji preprocessing dan leaf validation pipeline
tanpa memerlukan TensorFlow (karena DLL issue).

Script ini akan:
1. Download gambar daun padi nyata dari internet (berbagai kelas penyakit)
2. Jalankan preprocessing pipeline baru (EXIF fix, letterbox, normalisasi)
3. Jalankan leaf validation (warna HSV, skin detection, dll)
4. Report apakah gambar diterima/ditolak dan alasannya
5. Verifikasi output normalisasi (range harus [-1.0, 1.0])

Jalankan dengan: python scripts/test_visual_pipeline.py
"""
import io
import sys
import os
import urllib.request
from pathlib import Path

# Tambahkan root project ke sys.path
PROJECT_ROOT = Path(__file__).resolve().parents[1]
sys.path.insert(0, str(PROJECT_ROOT))

try:
    import cv2
    import numpy as np
    print("✅ cv2 dan numpy tersedia")
except ImportError as e:
    print(f"❌ Import error: {e}")
    sys.exit(1)

try:
    from PIL import Image as PilImage
    print(f"✅ Pillow tersedia: {PilImage.__version__}")
except ImportError:
    print("⚠️  Pillow tidak tersedia, EXIF fix akan skip")

from app.infrastructure.machine_learning.image_preprocessor import ImagePreprocessor
from app.domain.services.leaf_validation_policy import LeafValidationPolicy
from app.domain.services.image_quality_policy import ImageQualityPolicy

# -----------------------------------------------------------------------
# Gambar test — Rice disease images dari Kaggle/GitHub public datasets
# Sumber: https://github.com/ultralytics/assets (open) dan direct URLs
# -----------------------------------------------------------------------
TEST_IMAGES = [
    # Daun padi SEHAT (hijau)
    {
        "label": "Daun Padi Sehat",
        "expected": "PASS",
        "url": "https://upload.wikimedia.org/wikipedia/commons/thumb/b/b2/Rice_leaf.jpg/320px-Rice_leaf.jpg",
    },
    # Blast (Penyakit Blas) — bintik abu-abu/putih dengan tepi cokelat
    {
        "label": "Blast Disease",
        "expected": "PASS",
        "url": "https://upload.wikimedia.org/wikipedia/commons/thumb/2/2f/Rice_blast_lesions.jpg/320px-Rice_blast_lesions.jpg",
    },
    # Brown Spot (Bercak Cokelat)
    {
        "label": "Brown Spot",
        "expected": "PASS",
        "url": "https://www.plantwise.org/KnowledgeBank/images/pests/img005640.jpg",
    },
    # Gambar NON-DAUN — wajah/selfie (harus DITOLAK)
    {
        "label": "Foto Wajah (HARUS DITOLAK)",
        "expected": "REJECT",
        "url": "https://upload.wikimedia.org/wikipedia/commons/thumb/1/14/Gatto_europeo4.jpg/240px-Gatto_europeo4.jpg",
    },
    # Gambar tanah sawah (mungkin DITOLAK karena kurang daun)
    {
        "label": "Tanah Sawah",
        "expected": "REJECT",
        "url": "https://upload.wikimedia.org/wikipedia/commons/thumb/9/97/The_Earth_seen_from_Apollo_17.jpg/240px-The_Earth_seen_from_Apollo_17.jpg",
    },
]

# Gambar lokal dari dataset yang mungkin sudah ada
LOCAL_IMAGE_PATHS = [
    Path(PROJECT_ROOT) / "tests" / "fixtures",
]


def download_image(url: str, timeout: int = 10) -> bytes | None:
    """Download gambar dari URL."""
    try:
        req = urllib.request.Request(
            url,
            headers={"User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36"}
        )
        with urllib.request.urlopen(req, timeout=timeout) as response:
            return response.read()
    except Exception as e:
        print(f"  ⚠️  Gagal download {url[:60]}...: {e}")
        return None


def create_synthetic_image(image_type: str) -> bytes:
    """Buat gambar sintetis untuk test jika download gagal."""
    img = None
    
    if image_type == "healthy_leaf":
        # Daun hijau cerah — simulasi daun sehat
        img = np.zeros((480, 640, 3), dtype=np.uint8)
        # Background hijau gelap
        img[:] = [30, 80, 30]
        # Daun hijau lebih terang di tengah
        cv2.ellipse(img, (320, 240), (200, 150), 0, 0, 360, (60, 160, 60), -1)
        cv2.ellipse(img, (320, 240), (180, 130), 0, 0, 360, (70, 180, 70), -1)
        # Urat daun
        cv2.line(img, (320, 90), (320, 390), (40, 120, 40), 3)
        
    elif image_type == "blast_leaf":
        # Daun dengan lesi blast — bintik cokelat/abu-abu
        img = np.zeros((480, 640, 3), dtype=np.uint8)
        img[:] = [30, 80, 30]
        cv2.ellipse(img, (320, 240), (220, 160), 0, 0, 360, (65, 150, 55), -1)
        # Tambah lesi blast (bentuk diamond/jarum)
        for i in range(15):
            x = np.random.randint(150, 500)
            y = np.random.randint(100, 380)
            # Lesi blast: ellipse kecil cokelat/abu-abu dengan tepi
            cv2.ellipse(img, (x, y), (np.random.randint(8, 20), np.random.randint(3, 8)), 
                       np.random.randint(0, 180), 0, 360, (120, 100, 60), -1)
            cv2.ellipse(img, (x, y), (np.random.randint(10, 25), np.random.randint(5, 10)), 
                       np.random.randint(0, 180), 0, 360, (90, 85, 70), 2)
        
    elif image_type == "brown_spot":
        # Daun dengan bercak cokelat bulat
        img = np.zeros((480, 640, 3), dtype=np.uint8)
        img[:] = [35, 85, 35]
        cv2.ellipse(img, (320, 240), (210, 155), 0, 0, 360, (60, 155, 50), -1)
        # Bercak cokelat bulat
        for i in range(20):
            x = np.random.randint(150, 480)
            y = np.random.randint(100, 380)
            r = np.random.randint(6, 16)
            cv2.circle(img, (x, y), r, (50, 80, 130), -1)  # cokelat
            cv2.circle(img, (x, y), r+2, (30, 50, 80), 2)   # tepi lebih gelap
        
    elif image_type == "non_leaf_skin":
        # Wajah/kulit manusia — harus ditolak
        img = np.zeros((480, 640, 3), dtype=np.uint8)
        # Warna kulit (BGR)
        img[:] = [140, 180, 210]
        cv2.ellipse(img, (320, 240), (150, 190), 0, 0, 360, (120, 160, 200), -1)
        # Mata
        cv2.ellipse(img, (260, 200), (25, 15), 0, 0, 360, (30, 30, 50), -1)
        cv2.ellipse(img, (380, 200), (25, 15), 0, 0, 360, (30, 30, 50), -1)
        
    elif image_type == "monochrome_doc":
        # Dokumen kertas/monokrom — harus ditolak
        img = np.ones((480, 640, 3), dtype=np.uint8) * 220  # abu-abu terang
        # Simulasi garis teks
        for i in range(10):
            y = 80 + i * 35
            cv2.line(img, (50, y), (590, y), (100, 100, 100), 2)
    
    if img is None:
        img = np.ones((480, 640, 3), dtype=np.uint8) * 128
    
    # Encode ke JPEG
    _, buf = cv2.imencode(".jpg", img, [cv2.IMWRITE_JPEG_QUALITY, 90])
    return buf.tobytes()


def analyze_image(label: str, content: bytes, preprocessor: ImagePreprocessor,
                  leaf_policy: LeafValidationPolicy, quality_policy: ImageQualityPolicy) -> dict:
    """Analisis gambar melalui pipeline lengkap."""
    result = {"label": label, "status": "ERROR", "details": {}}
    
    try:
        # 1. Decode dengan EXIF fix
        image_rgb = preprocessor.decode(content)
        h, w = image_rgb.shape[:2]
        result["details"]["resolution"] = f"{w}×{h}"
        
        # 2. Quality measurement
        blur_score, brightness_score = preprocessor.measure_quality(image_rgb)
        result["details"]["blur_score"] = round(blur_score, 1)
        result["details"]["brightness"] = round(brightness_score, 1)
        
        # 3. Quality gate
        quality_decision = quality_policy.evaluate(blur_score, brightness_score, h, w)
        if not quality_decision.is_acceptable:
            result["status"] = f"REJECTED (Quality: {quality_decision.error_code})"
            result["details"]["reject_reason"] = quality_decision.error_message
            return result
        
        # 4. Leaf feature analysis
        leaf_features = preprocessor.analyze_leaf_features(image_rgb)
        result["details"]["leaf_features"] = {
            k: f"{v:.3f}" if isinstance(v, float) else v
            for k, v in leaf_features.items()
        }
        
        # 5. Leaf validation
        leaf_decision = leaf_policy.evaluate_visual_features(
            leaf_ratio=leaf_features["leaf_ratio"],
            skin_ratio=leaf_features["skin_ratio"],
            mean_saturation=leaf_features["mean_saturation"],
            unnatural_ratio=leaf_features["unnatural_ratio"],
            green_ratio=leaf_features.get("green_ratio", leaf_features["leaf_ratio"]),
        )
        
        if not leaf_decision.is_acceptable:
            result["status"] = f"REJECTED (Leaf: {leaf_decision.error_code})"
            result["details"]["reject_reason"] = leaf_decision.error_message
            return result
        
        # 6. Preprocessing untuk model
        model_input = preprocessor.preprocess_for_model(image_rgb)
        
        # Verifikasi normalisasi: range harus [-1.0, 1.0]
        min_val = float(model_input.min())
        max_val = float(model_input.max())
        shape = model_input.shape
        
        norm_ok = -1.05 <= min_val and max_val <= 1.05
        result["details"]["model_input"] = {
            "shape": str(shape),
            "min": round(min_val, 4),
            "max": round(max_val, 4),
            "normalization_ok": "✅ [-1, 1]" if norm_ok else f"❌ WRONG [{min_val:.2f}, {max_val:.2f}]"
        }
        
        result["status"] = "PASSED"
        if leaf_decision.warnings:
            result["details"]["warnings"] = leaf_decision.warnings
        
    except Exception as e:
        result["status"] = f"ERROR: {type(e).__name__}: {e}"
    
    return result


def print_result(result: dict) -> None:
    """Print hasil analisis dengan format yang jelas."""
    label = result["label"]
    status = result["status"]
    
    status_icon = "✅" if "PASSED" in status else ("❌" if "REJECTED" in status or "ERROR" in status else "⚠️")
    print(f"\n{'='*60}")
    print(f"{status_icon} [{label}]")
    print(f"   Status : {status}")
    
    details = result.get("details", {})
    
    if "resolution" in details:
        print(f"   Resolusi   : {details['resolution']}")
    if "blur_score" in details:
        print(f"   Blur Score : {details['blur_score']} (min: 80)")
    if "brightness" in details:
        print(f"   Brightness : {details['brightness']} (range: 35-225)")
    if "reject_reason" in details:
        print(f"   Alasan     : {details['reject_reason']}")
    if "leaf_features" in details:
        feats = details["leaf_features"]
        print(f"   Leaf Ratio : {feats.get('leaf_ratio', '-')} (min: 0.18)")
        print(f"   Green Ratio: {feats.get('green_ratio', '-')}")
        print(f"   Skin Ratio : {feats.get('skin_ratio', '-')} (max: 0.28)")
        print(f"   Saturation : {feats.get('mean_saturation', '-')}")
    if "model_input" in details:
        mi = details["model_input"]
        print(f"   Shape      : {mi['shape']}")
        print(f"   Normalisasi: {mi['normalization_ok']}")
    if "warnings" in details:
        for w in details["warnings"]:
            print(f"   ⚠️  {w}")


def main():
    print("=" * 60)
    print("P.A.D.I. AI Pipeline — Visual Test Suite")
    print("=" * 60)
    
    preprocessor = ImagePreprocessor()
    leaf_policy = LeafValidationPolicy(min_leaf_ratio=0.18, min_disease_confidence=0.35)
    quality_policy = ImageQualityPolicy(min_blur_score=80, min_brightness=35, max_brightness=225)
    
    # Gambar sintetis yang selalu tersedia (tidak butuh internet)
    synthetic_tests = [
        ("Daun Padi Sehat (Sintetis)", "healthy_leaf", "PASS"),
        ("Blast Disease (Sintetis)", "blast_leaf", "PASS"),
        ("Brown Spot (Sintetis)", "brown_spot", "PASS"),
        ("Wajah/Kulit Manusia (Sintetis)", "non_leaf_skin", "REJECT"),
        ("Dokumen Monokrom (Sintetis)", "monochrome_doc", "REJECT"),
    ]
    
    passed = 0
    failed = 0
    total = 0
    
    print("\n📷 Testing Gambar Sintetis (offline)...")
    for label, img_type, expected in synthetic_tests:
        total += 1
        content = create_synthetic_image(img_type)
        result = analyze_image(label, content, preprocessor, leaf_policy, quality_policy)
        print_result(result)
        
        is_pass = "PASSED" in result["status"]
        is_reject = "REJECTED" in result["status"]
        
        if expected == "PASS" and is_pass:
            print(f"   ✅ CORRECT (diharapkan: PASS)")
            passed += 1
        elif expected == "REJECT" and is_reject:
            print(f"   ✅ CORRECT (diharapkan: REJECT)")
            passed += 1
        else:
            print(f"   ❌ WRONG! Diharapkan: {expected}, Dapat: {result['status']}")
            failed += 1
    
    # Test gambar dari internet (jika tersedia)
    print("\n\n📡 Testing Gambar Online (butuh internet)...")
    for test in TEST_IMAGES:
        total += 1
        label = test["label"]
        expected = test["expected"]
        
        print(f"\n   Downloading: {test['url'][:70]}...")
        content = download_image(test["url"])
        
        if content is None:
            print(f"   ⏭️  Skip (tidak dapat download)")
            total -= 1
            continue
        
        result = analyze_image(label, content, preprocessor, leaf_policy, quality_policy)
        print_result(result)
        
        is_pass = "PASSED" in result["status"]
        is_reject = "REJECTED" in result["status"]
        
        if expected == "PASS" and is_pass:
            print(f"   ✅ CORRECT (diharapkan: PASS)")
            passed += 1
        elif expected == "REJECT" and is_reject:
            print(f"   ✅ CORRECT (diharapkan: REJECT)")
            passed += 1
        else:
            print(f"   ⚠️  MISMATCH — Diharapkan: {expected}, Dapat: {result['status']}")
            failed += 1
    
    # SUMMARY
    print(f"\n{'='*60}")
    print(f"📊 SUMMARY: {passed}/{total} CORRECT | {failed} MISMATCH")
    if failed == 0:
        print("🎉 SEMUA TEST LULUS — Pipeline berfungsi dengan benar!")
    else:
        print(f"⚠️  {failed} test tidak sesuai ekspektasi — perlu kalibrasi threshold")
    print("=" * 60)
    
    return 0 if failed == 0 else 1


if __name__ == "__main__":
    np.random.seed(42)
    sys.exit(main())
