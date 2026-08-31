SUPPORTED_IMAGE_SIGNATURES = {
    "image/jpeg": (b"\xff\xd8\xff",),
    "image/png": (b"\x89PNG\r\n\x1a\n",),
}

SUPPORTED_DISEASE_CODES = {
    "bacterial_leaf_blight": "Bacterial Leaf Blight (Hawar Daun Bakteri)",
    "bacterial_leaf_streak": "Bacterial Leaf Streak (Bercak Daun Bakteri)",
    "bacterial_panicle_blight": "Bacterial Panicle Blight (Hawar Malai Bakteri)",
    "blast": "Blast (Penyakit Blas)",
    "brown_spot": "Brown Spot (Bercak Cokelat)",
    "dead_heart": "Dead Heart (Penggerek Batang)",
    "downy_mildew": "Downy Mildew (Bulu Embun)",
    "hispa": "Hispa (Hama Hispa)",
    "healthy": "Normal (Padi Sehat)",
    "tungro": "Tungro (Penyakit Tungro)",
    "unknown": "Tidak Dapat Dipastikan",
}

DISCLAIMER_TEXT = (
    "Hasil AI adalah pendukung keputusan dan bukan pengganti diagnosis resmi "
    "dari penyuluh pertanian, agronom, atau laboratorium."
)
