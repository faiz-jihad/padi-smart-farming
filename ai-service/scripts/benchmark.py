"""
scripts/benchmark.py — Benchmark P.A.D.I. AI Service

Mengukur:
- Cold latency (request pertama)
- Warm latency (request berikutnya)
- p50, p95, p99, mean
- images/sec throughput

Usage:
    python scripts/benchmark.py [--url URL] [--image PATH] [--n N] [--warmup W]

Example:
    python scripts/benchmark.py --url http://localhost:8002 --n 50 --warmup 5
"""
from __future__ import annotations

import argparse
import io
import statistics
import time

import numpy as np

try:
    import httpx
except ImportError:
    print("ERROR: httpx tidak terinstall. Jalankan: pip install httpx")
    raise


try:
    from PIL import Image
except ImportError:
    print("ERROR: Pillow tidak terinstall. Jalankan: pip install Pillow")
    raise


def make_synthetic_leaf_jpeg(width: int = 640, height: int = 480) -> bytes:
    """Buat gambar JPEG sintetis (daun hijau)."""
    arr = np.zeros((height, width, 3), dtype=np.uint8)
    arr[:, :, 1] = 120  # Hijau
    arr[:, :, 0] = 30
    arr[:, :, 2] = 30
    img = Image.fromarray(arr, mode="RGB")
    buf = io.BytesIO()
    img.save(buf, format="JPEG", quality=85)
    return buf.getvalue()


def run_request(client: httpx.Client, url: str, image_bytes: bytes) -> float:
    """Jalankan satu request, return latency dalam ms."""
    t0 = time.perf_counter()
    response = client.post(
        f"{url}/api/v1/ai/padi/diagnose",
        files={"image": ("benchmark.jpg", image_bytes, "image/jpeg")},
        data={"locale": "id"},
        timeout=60.0,
    )
    elapsed = (time.perf_counter() - t0) * 1000

    if response.status_code != 200:
        print(f"  WARNING: status={response.status_code} body={response.text[:200]}")

    return elapsed


def main():
    parser = argparse.ArgumentParser(description="P.A.D.I. AI Service Benchmark")
    parser.add_argument("--url", default="http://localhost:8002", help="Base URL service")
    parser.add_argument("--image", default=None, help="Path ke gambar JPEG/PNG (opsional)")
    parser.add_argument("--n", type=int, default=20, help="Jumlah request benchmark")
    parser.add_argument("--warmup", type=int, default=3, help="Jumlah warmup request")
    args = parser.parse_args()

    print(f"\n{'='*60}")
    print(f"P.A.D.I. AI Service Benchmark")
    print(f"Target URL : {args.url}")
    print(f"Warmup     : {args.warmup} requests")
    print(f"Benchmark  : {args.n} requests")
    print(f"{'='*60}\n")

    # Load gambar
    if args.image:
        print(f"Membaca gambar dari: {args.image}")
        with open(args.image, "rb") as f:
            image_bytes = f.read()
    else:
        print("Menggunakan gambar sintetis (daun hijau 640x480)...")
        image_bytes = make_synthetic_leaf_jpeg()

    print(f"Ukuran gambar: {len(image_bytes) / 1024:.1f} KB\n")

    with httpx.Client() as client:
        # Health check
        try:
            r = client.get(f"{args.url}/health", timeout=5.0)
            if r.status_code == 200:
                print(f"Health check OK: {r.json()}")
            else:
                print(f"WARNING: Health check gagal: status={r.status_code}")
        except Exception as e:
            print(f"ERROR: Tidak dapat terhubung ke {args.url}: {e}")
            return

        # Cold request (pertama kali)
        print("\n--- Cold Request ---")
        cold_latency = run_request(client, args.url, image_bytes)
        print(f"Cold latency: {cold_latency:.1f} ms")

        # Warmup
        print(f"\n--- Warmup ({args.warmup} requests) ---")
        for i in range(args.warmup):
            lat = run_request(client, args.url, image_bytes)
            print(f"  warmup[{i+1}]: {lat:.1f} ms")

        # Benchmark
        print(f"\n--- Benchmark ({args.n} requests) ---")
        latencies: list[float] = []
        errors = 0

        for i in range(args.n):
            try:
                lat = run_request(client, args.url, image_bytes)
                latencies.append(lat)
                if (i + 1) % 10 == 0:
                    print(f"  Progress: {i+1}/{args.n} — last={lat:.1f}ms")
            except Exception as e:
                errors += 1
                print(f"  ERROR request {i+1}: {e}")

        # Statistik
        if not latencies:
            print("\nERROR: Tidak ada data latency (semua request gagal).")
            return

        latencies.sort()
        n = len(latencies)
        mean_lat = statistics.mean(latencies)
        median_lat = statistics.median(latencies)
        p95_lat = latencies[int(n * 0.95)]
        p99_lat = latencies[int(n * 0.99)] if n >= 100 else latencies[-1]
        min_lat = min(latencies)
        max_lat = max(latencies)
        throughput = 1000 / mean_lat  # requests/sec

        print(f"\n{'='*60}")
        print(f"BENCHMARK RESULTS")
        print(f"{'='*60}")
        print(f"Requests OK   : {n}/{args.n}")
        print(f"Errors        : {errors}")
        print(f"")
        print(f"Cold latency  : {cold_latency:.1f} ms")
        print(f"Mean          : {mean_lat:.1f} ms")
        print(f"Median (p50)  : {median_lat:.1f} ms")
        print(f"p95           : {p95_lat:.1f} ms")
        print(f"p99           : {p99_lat:.1f} ms")
        print(f"Min           : {min_lat:.1f} ms")
        print(f"Max           : {max_lat:.1f} ms")
        print(f"Throughput    : {throughput:.2f} req/sec")
        print(f"{'='*60}\n")


if __name__ == "__main__":
    main()
