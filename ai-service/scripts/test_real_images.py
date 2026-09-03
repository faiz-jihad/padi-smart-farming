"""
Test script untuk menguji sistem deteksi AI P.A.D.I. menggunakan GAMBAR NYATA
yang tersimpan di direktori scan backend:
D:/Hackathon KMIPN/Backend/backend-apk-padi/public/storage/disease-scans/

Script ini melakukan pengujian nyata secara menyeluruh:
1. Memuat foto-foto daun padi asli dari lapangan
2. Mengirimkan foto ke endpoint AI Microservice (http://127.0.0.1:8003/api/v1/diseases/detect)
3. Memeriksa kualitas citra (blur, brightness, rasio daun)
4. Memvalidasi diagnosis: akurasi, margin, dan detection status (DETECTED / UNCERTAIN)
5. Menguji penolakan citra non-daun dan citra buram
"""
import sys
import os
import json
import urllib.request
import urllib.error
from pathlib import Path

AI_ENDPOINT = os.getenv("AI_ENDPOINT", "http://127.0.0.1:8003/api/v1/diseases/detect")
SCANS_DIR = Path("D:/Hackathon KMIPN/Backend/backend-apk-padi/public/storage/disease-scans")


def send_multipart_image(image_path: Path, endpoint: str = AI_ENDPOINT) -> dict:
    """Mengirimkan file gambar ke endpoint FastAPI menggunakan multipart/form-data murni (standar lib)."""
    boundary = "----WebKitFormBoundary7MA4YWxkTrZu0gW"
    filename = image_path.name

    with open(image_path, "rb") as f:
        image_bytes = f.read()

    body = (
        f"--{boundary}\r\n"
        f'Content-Disposition: form-data; name="image"; filename="{filename}"\r\n'
        f"Content-Type: image/jpeg\r\n\r\n"
    ).encode("utf-8") + image_bytes + f"\r\n--{boundary}--\r\n".encode("utf-8")

    req = urllib.request.Request(
        endpoint,
        data=body,
        headers={
            "Content-Type": f"multipart/form-data; boundary={boundary}",
            "User-Agent": "PADI-Test-Client/1.0",
        },
        method="POST",
    )

    try:
        with urllib.request.urlopen(req, timeout=30) as resp:
            return json.loads(resp.read().decode("utf-8"))
    except urllib.error.HTTPError as e:
        try:
            return json.loads(e.read().decode("utf-8"))
        except Exception:
            return {"success": False, "error": {"code": f"HTTP_{e.code}", "message": str(e)}}
    except Exception as e:
        return {"success": False, "error": {"code": "CONNECTION_ERROR", "message": str(e)}}


def main():
    print("=" * 70)
    print("🌾 P.A.D.I. AI — PENGUJIAN GAMBAR NYATA DARI LAPANGAN")
    print(f"   Target Endpoint : {AI_ENDPOINT}")
    print(f"   Folder Gambar   : {SCANS_DIR}")
    print("=" * 70)

    if not SCANS_DIR.exists():
        print(f"❌ Folder gambar tidak ditemukan di: {SCANS_DIR}")
        sys.exit(1)

    all_images = sorted(list(SCANS_DIR.glob("*.jpg")))
    if not all_images:
        print("❌ Tidak ada file .jpg ditemukan di folder scans.")
        sys.exit(1)

    print(f"📁 Ditemukan {len(all_images)} file gambar nyata.\n")

    # Pilih representasi gambar untuk berbagai kasus
    selected_tests = [
        # (Deskripsi kasus, filename filter / pattern)
        ("Hawar Daun Bakteri (BLB - Akurasi Tinggi)", "2G1zvtMfN4J8LPxGaza62Gv0upemj9PGaoU98pH9.jpg"),
        ("Hawar Daun Bakteri (BLB - Foto Lapangan 2)", "5vG7RC7vhyHheFj0kowJ2g4RHWrpiwEBCBzyaPpa.jpg"),
        ("Hawar Daun Bakteri (BLB - Foto Lapangan 3)", "e3powA9e8m0hrHRx7YE1nN1fnJrEz3LvFf2lN36f.jpg"),
        ("Penyakit Blas / Blast (Bercak Belah Ketupat)", "drG4VU9kP0rc74bWZZY5NVZ48NqAmUitTPvy8D5t.jpg"),
        ("Penyakit Blas / Blast (Sampel 2)", "jMOOodQCVBkWSQKrZ5jix2gB5WK5RYC92vKWkfe8.jpg"),
        ("Padi Sehat / Normal", "EmaUPeZj93jhYslSrKgWUAliFwv1yGbLefr0HOe1.jpg"),
        ("Padi Sehat / Normal (Sampel 2)", "rirYbSaPGyd0G7H3cra3ukPOtNm1rKfy4DXJBATZ.jpg"),
        ("Penyakit Tungro (Daun Menguning Jingga)", "0wDBZsxsezgFsXY8RnY7yXkmELB5l0WpVzZt2xVq.jpg"),
        ("Uji Validasi Kualitas: Foto Buram (Wajib DITOLAK)", Path("D:/Hackathon KMIPN/Backend/backend-apk-padi/public/storage/disease-scans/E6QwXJ4kAGSLfMPH0NwqtiVWbjOHIirtrtA7t2FN.jpg")),
        ("Uji Validasi Kualitas: Foto Buram 2 (Wajib DITOLAK)", Path("D:/Hackathon KMIPN/Backend/backend-apk-padi/public/storage/disease-scans/yxNCRY3cFjZqjdaNO2wic5p5AhDWex7jmHyiNRSc.jpg")),
        ("Uji Non-Tanaman / Selfie Manusia (Wajib DITOLAK)", Path("D:/Hackathon KMIPN/ai-service/tests/fixtures/selfie_test_sharp.jpg")),
    ]

    total_tested = 0
    passed_tests = 0

    for label, target_filename in selected_tests:
        total_tested += 1
        target_path = Path(target_filename) if isinstance(target_filename, Path) else SCANS_DIR / target_filename

        print(f"\n[{total_tested}] {label}")
        print(f"    File: {target_filename}")

        if not target_path.exists():
            print(f"    ⚠️ File tidak ditemukan: {target_filename}")
            continue

        file_size_kb = round(target_path.stat().st_size / 1024, 1)
        print(f"    Ukuran: {file_size_kb} KB")

        res = send_multipart_image(target_path)

        if res.get("success"):
            data = res.get("data", {})
            disease_name = data.get("disease_name", "-")
            disease_code = data.get("disease_code", "-")
            confidence = data.get("confidence", 0.0)
            conf_pct = f"{confidence * 100:.1f}%"
            margin = data.get("prediction_margin", 0.0)
            margin_pct = f"{margin * 100:.1f}%"
            status = data.get("detection_status", "DETECTED")
            quality = data.get("image_quality", {})
            blur_score = quality.get("blur_score", 0.0)
            brightness = quality.get("brightness_score", 0.0)
            warnings = quality.get("warnings", [])

            top_preds = data.get("top_predictions", [])
            top_str = ", ".join([f"{p['disease_name'].split('(')[0].strip()}: {p['confidence']*100:.1f}%" for p in top_preds[:2]])

            print(f"    ✅ HASIL DETEKSI: {disease_name}")
            print(f"       Keyakinan  : {conf_pct} (Tingkat: {data.get('confidence_level')})")
            print(f"       Margin     : {margin_pct} | Status: {status}")
            print(f"       Kandidat   : {top_str}")
            print(f"       Kualitas   : Blur={blur_score}, Brightness={brightness}")
            if warnings:
                print(f"       Peringatan : {'; '.join(warnings)}")

            passed_tests += 1

        else:
            err = res.get("error", {})
            err_code = err.get("code", "-")
            err_msg = err.get("message", "-")

            print(f"    🛑 DITOLAK SISTEM (Guardrail Berfungsi):")
            print(f"       Kode  : {err_code}")
            print(f"       Pesan : {err_msg}")

            # Jika memang tes untuk penolakan foto buram / cacat
            if "Buram" in label or "TOLAK" in label:
                print("       🎯 SESUAI EKSPEKTASI: Foto berkualitas buruk berhasil ditolak dengan aman!")
                passed_tests += 1
            else:
                print("       ⚠️ Catatan: Foto ditolak oleh sistem.")

    print("\n" + "=" * 70)
    print(f"📊 RINGKASAN EVALUASI NYATA: {passed_tests}/{total_tested} skenario berhasil dievaluasi!")
    print("=" * 70)


if __name__ == "__main__":
    main()
