SUPPORTED_IMAGE_SIGNATURES = {
    "image/jpeg": (b"\xff\xd8\xff",),
    "image/png": (b"\x89PNG\r\n\x1a\n",),
}

SUPPORTED_DISEASE_CODES = {
    "healthy": "Healthy",
    "blast": "Blast",
    "tungro": "Tungro",
    "bacterial_leaf_blight": "Bacterial Leaf Blight",
    "unknown": "Tidak Dapat Dipastikan",
}

DISCLAIMER_TEXT = (
    "Hasil AI adalah pendukung keputusan dan bukan pengganti diagnosis resmi "
    "dari penyuluh pertanian, agronom, atau laboratorium."
)
