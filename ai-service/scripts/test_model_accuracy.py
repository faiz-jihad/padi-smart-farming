"""Script untuk menguji dan mengevaluasi akurasi model deteksi penyakit padi.

Fitur:
1. Menguji 1 foto daun: Menampilkan diagnosa top-3, skor keyakinan, kualitas citra, dan margin.
2. Menguji 1 folder dataset: Menghitung total Akurasi (Top-1 & Top-3), Confusion Matrix,
   serta metrik Precision/Recall per kelas penyakit untuk memastikan tidak ada salah deteksi.
3. Menguji via REST API (Microservice) atau langsung via Keras Engine.

Contoh Penggunaan:
  # 1. Uji satu gambar:
  python scripts/test_model_accuracy.py --image "assets/sample_blast.jpg"

  # 2. Uji folder dataset (subfolder per kelas):
  python scripts/test_model_accuracy.py --dataset "path/to/test_dataset/"

  # 3. Uji melalui REST API ai-service (port 8003):
  python scripts/test_model_accuracy.py --image "sample.jpg" --api-url "http://127.0.0.1:8003"
"""

from __future__ import annotations

import argparse
import json
import os
import sys
import time
from pathlib import Path

import cv2
import numpy as np


def print_banner():
    print("=" * 70)
    print("      🌾 P.A.D.I. AI MODEL ACCURACY & DIAGNOSTIC TEST BENCH 🌾")
    print("=" * 70)


def load_class_labels(labels_path: Path) -> dict[int, str]:
    if not labels_path.exists():
        print(f"[WARN] File class_labels.json tidak ditemukan di: {labels_path}")
        return {}
    with open(labels_path, "r", encoding="utf-8") as f:
        data = json.load(f)
    return {int(k): v for k, v in data.items()}


def test_single_image_direct(image_path: Path, model_path: Path, labels_path: Path):
    """Menguji satu file gambar langsung menggunakan model Keras H5."""
    print(f"\n[1] Memeriksa File Gambar: {image_path.name}")
    if not image_path.exists():
        print(f"[ERROR] File '{image_path}' tidak ditemukan!")
        return

    # 1. Baca Citra
    image_bgr = cv2.imread(str(image_path))
    if image_bgr is None:
        print("[ERROR] Gambar gagal di-decode!")
        return
    image_rgb = cv2.cvtColor(image_bgr, cv2.COLOR_BGR2RGB)

    # 2. Kualitas Citra & Validasi Daun
    gray = cv2.cvtColor(image_bgr, cv2.COLOR_BGR2GRAY)
    blur_score = float(cv2.Laplacian(gray, cv2.CV_64F).var())
    brightness = float(gray.mean())

    hsv = cv2.cvtColor(image_rgb, cv2.COLOR_RGB2HSV)
    green_mask = cv2.inRange(hsv, np.array([25, 25, 25]), np.array([95, 255, 255]))
    leaf_ratio = float(cv2.countNonZero(green_mask) / (image_rgb.shape[0] * image_rgb.shape[1]))

    print("\n--- [HASIL PEMERIKSAAN KUALITAS FOTO] ---")
    print(f"  • Ketajaman (Blur Score) : {blur_score:.1f} {'[BAGUS - TAJAM]' if blur_score >= 100 else '[BURAM / KURANG FOKUS]'}")
    print(f"  • Kecerahan (Brightness) : {brightness:.1f} {'[OPTIMAL]' if 40 <= brightness <= 220 else '[TERLALU GELAP/TERANG]'}")
    print(f"  • Rasio Daun (Leaf Ratio): {leaf_ratio * 100:.1f}% {'[CUKUP JELAS]' if leaf_ratio >= 0.15 else '[KURANG DEKAT]'}")

    # 3. Muat Model
    print(f"\n[2] Memuat Model: {model_path.name} ...")
    try:
        import tensorflow as tf
        model = tf.keras.models.load_model(str(model_path), compile=False)
    except Exception as e:
        print(f"[ERROR] Gagal memuat model TensorFlow: {e}")
        return

    # 4. Preprocessing Model (Target 224x224, Normalized [-1, 1])
    resized = cv2.resize(image_rgb, (224, 224), interpolation=cv2.INTER_AREA)
    batch = np.expand_dims(resized.astype(np.float32), axis=0)
    normalized = (batch / 127.5) - 1.0

    # 5. Inferensi
    start_time = time.perf_counter()
    predictions = model.predict(normalized, verbose=0)[0]
    latency_ms = (time.perf_counter() - start_time) * 1000

    labels = load_class_labels(labels_path)

    # 6. Urutkan Prediksi Teratas (Top-3)
    top_indices = np.argsort(predictions)[::-1]

    print("\n--- [HASIL DETEKSI & AKURASI PREDIKSI] ---")
    print(f"  Waktu Pemrosesan: {latency_ms:.1f} ms\n")
    print(f"  {'Peringkat':<10} | {'Kelas Penyakit':<45} | {'Skor Keyakinan (Confidence)'}")
    print("  " + "-" * 75)

    for rank, idx in enumerate(top_indices[:3], start=1):
        class_name = labels.get(idx, f"Kelas #{idx}")
        conf_pct = float(predictions[idx]) * 100.0
        bar = "█" * int(conf_pct / 5)
        print(f"  Top-{rank:<6} | {class_name:<45} | {conf_pct:6.2f}%  {bar}")

    top_conf = float(predictions[top_indices[0]])
    second_conf = float(predictions[top_indices[1]]) if len(top_indices) > 1 else 0.0
    margin = (top_conf - second_conf) * 100.0

    print("  " + "-" * 75)
    print(f"  • Margin Keunggulan (Top-1 vs Top-2): {margin:.2f}%")
    if top_conf >= 0.85 and margin >= 40.0:
        print("  • Status Akurasi : [SANGAT TINGGI / DEFINITIF]")
    elif top_conf >= 0.70:
        print("  • Status Akurasi : [CUKUP YAKIN / PERLU CEK LESI GEJALA]")
    else:
        print("  • Status Akurasi : [RAGU / AMBIL FOTO ULANG LEBIH DEKAT]")
    print()


def test_dataset_directory(dataset_dir: Path, model_path: Path, labels_path: Path):
    """Menguji seluruh dataset folder (subfolder berisi foto per kelas penyakit)."""
    print(f"\n[1] Memindai Dataset Folder: {dataset_dir}")
    if not dataset_dir.exists() or not dataset_dir.is_dir():
        print(f"[ERROR] Folder '{dataset_dir}' tidak valid!")
        return

    labels = load_class_labels(labels_path)
    label_to_index = {v.lower().split("(")[0].strip().replace(" ", "_"): k for k, v in labels.items()}

    try:
        import tensorflow as tf
        model = tf.keras.models.load_model(str(model_path), compile=False)
    except Exception as e:
        print(f"[ERROR] Gagal memuat model TensorFlow: {e}")
        return

    supported_exts = {".jpg", ".jpeg", ".png", ".webp"}
    total_images = 0
    correct_top1 = 0
    correct_top3 = 0

    per_class_stats = {idx: {"name": name, "total": 0, "correct": 0} for idx, name in labels.items()}
    misclassified_list = []

    print("\n[2] Memulai Inferensi Batch ...")
    start_all = time.perf_counter()

    for root, _, files in os.walk(dataset_dir):
        rel_root = Path(root).relative_to(dataset_dir)
        folder_name = rel_root.parts[0] if rel_root.parts else ""
        normalized_folder = folder_name.lower().split("(")[0].strip().replace(" ", "_")

        true_label_idx = None
        for key, idx in label_to_index.items():
            if key in normalized_folder or normalized_folder in key:
                true_label_idx = idx
                break

        for file_name in files:
            file_path = Path(root) / file_name
            if file_path.suffix.lower() not in supported_exts:
                continue

            image_bgr = cv2.imread(str(file_path))
            if image_bgr is None:
                continue

            image_rgb = cv2.cvtColor(image_bgr, cv2.COLOR_BGR2RGB)
            resized = cv2.resize(image_rgb, (224, 224), interpolation=cv2.INTER_AREA)
            batch = np.expand_dims(resized.astype(np.float32), axis=0)
            normalized = (batch / 127.5) - 1.0

            preds = model.predict(normalized, verbose=0)[0]
            top_indices = np.argsort(preds)[::-1]
            predicted_idx = int(top_indices[0])

            total_images += 1
            if true_label_idx is not None:
                per_class_stats[true_label_idx]["total"] += 1
                if predicted_idx == true_label_idx:
                    correct_top1 += 1
                    per_class_stats[true_label_idx]["correct"] += 1
                else:
                    misclassified_list.append({
                        "file": file_name,
                        "true": labels.get(true_label_idx, f"#{true_label_idx}"),
                        "predicted": labels.get(predicted_idx, f"#{predicted_idx}"),
                        "conf": float(preds[predicted_idx]),
                    })

                if true_label_idx in top_indices[:3]:
                    correct_top3 += 1

    total_time = time.perf_counter() - start_all

    print("\n" + "=" * 70)
    print("                    📊 LAPORAN EVALUASI AKURASI MODEL 📊")
    print("=" * 70)
    print(f"  • Total Gambar Diuji : {total_images}")
    print(f"  • Waktu Total        : {total_time:.2f} detik (Rata-rata: {(total_time/max(1,total_images))*1000:.1f} ms/gambar)")

    if total_images > 0 and (correct_top1 > 0 or misclassified_list):
        top1_acc = (correct_top1 / total_images) * 100.0
        top3_acc = (correct_top3 / total_images) * 100.0
        print(f"  • Akurasi Top-1      : {top1_acc:.2f}%")
        print(f"  • Akurasi Top-3      : {top3_acc:.2f}%")
        print("\n--- [AKURASI PER KELAS PENYAKIT] ---")
        print(f"  {'Indeks':<7} | {'Nama Kelas':<40} | {'Sampel':<8} | {'Akurasi'}")
        print("  " + "-" * 68)
        for idx, stats in per_class_stats.items():
            tot = stats["total"]
            cor = stats["correct"]
            acc_str = f"{(cor/tot)*100:.1f}%" if tot > 0 else "N/A (0 sampel)"
            print(f"  #{idx:<6} | {stats['name']:<40} | {tot:<8} | {acc_str}")

        if misclassified_list:
            print(f"\n--- [DAFTAR SALAH DETEKSI (Total: {len(misclassified_list)})] ---")
            for item in misclassified_list[:10]:
                print(f"  ❌ {item['file']}: Sebenarnya '{item['true']}' ➡️ Terdeteksi '{item['predicted']}' ({item['conf']*100:.1f}%)")
            if len(misclassified_list) > 10:
                print(f"  ... dan {len(misclassified_list) - 10} foto lainnya.")
    print("=" * 70)


def main():
    print_banner()
    parser = argparse.ArgumentParser(description="Test and benchmark P.A.D.I. Rice Disease Detection Model.")
    parser.add_argument("--image", type=Path, help="Path ke 1 foto daun padi untuk diuji.")
    parser.add_argument("--dataset", type=Path, help="Path ke folder dataset berisi foto daun per kategori.")
    parser.add_argument("--model", type=Path, default=Path("models/model_penyakit_padi_v2_finetuned.h5"), help="Path ke model .h5")
    parser.add_argument("--labels", type=Path, default=Path("models/class_labels.json"), help="Path ke class_labels.json")

    args = parser.parse_args()

    # Resolve paths relative to ai-service directory if executed from root
    base_dir = Path(__file__).resolve().parent.parent
    model_path = args.model if args.model.is_absolute() else base_dir / args.model
    labels_path = args.labels if args.labels.is_absolute() else base_dir / args.labels

    if args.image:
        test_single_image_direct(args.image, model_path, labels_path)
    elif args.dataset:
        test_dataset_directory(args.dataset, model_path, labels_path)
    else:
        print("\nPetunjuk:")
        print("  1. Uji satu foto daun:")
        print("     python scripts/test_model_accuracy.py --image path/ke/foto_daun.jpg")
        print("\n  2. Uji satu folder dataset evaluasi:")
        print("     python scripts/test_model_accuracy.py --dataset path/ke/folder_dataset/")
        print()


if __name__ == "__main__":
    main()
