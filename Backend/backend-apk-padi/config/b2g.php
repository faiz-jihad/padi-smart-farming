<?php

return [
    'plan_name' => 'Government Data Access',
    'plan_days' => (int) env('GOVERNMENT_SUBSCRIPTION_DAYS', 30),
    'plan_price' => (float) env('GOVERNMENT_SUBSCRIPTION_PRICE', 600000),
    'currency' => 'IDR',
    'whatsapp_number' => env('B2G_WHATSAPP_NUMBER', '628321163909'),
    'base_api_url' => env('APP_URL', 'http://127.0.0.1:8000') . '/api/v1/government',

    'plans' => [
        'payg' => [
            'code' => 'payg',
            'name' => 'Pay As You Go',
            'tagline' => 'Mulai kecil, bayar sesuai kebutuhan data.',
            'days' => (int) env('GOVERNMENT_PAYG_DAYS', 7),
            'price' => (float) env('GOVERNMENT_PAYG_PRICE', 0),
            'billing_label' => 'Tagihan dihitung sesuai pemakaian',
            'quota_label' => 'Cocok untuk uji coba, piloting kecamatan, atau integrasi awal.',
            'features' => [
                'Akses data agregat wilayah terbatas sesuai permintaan.',
                'Endpoint overview, penyakit, kegiatan, produktivitas, dan insight.',
                'Token API sementara untuk uji integrasi dashboard instansi.',
                'Admin P.A.D.I. menetapkan tagihan final setelah cakupan data disepakati.',
            ],
        ],

        'package_600' => [
            'code' => 'package_600',
            'name' => 'Paket 600',
            'tagline' => 'Paket tetap untuk akses B2G siap operasional.',
            'days' => (int) env('GOVERNMENT_PACKAGE_600_DAYS', 30),
            'price' => (float) env('GOVERNMENT_PACKAGE_600_PRICE', 600000),
            'billing_label' => 'Rp 600.000 / 30 hari',
            'quota_label' => 'Cocok untuk dinas/instansi yang ingin langsung memakai data rutin.',
            'features' => [
                'Akses 30 hari ke seluruh endpoint utama B2G.',
                'Ringkasan petani, lahan, aktivitas, penyakit, produktivitas, dan early warning.',
                'Token API 6 digit setelah pembayaran dan verifikasi admin.',
                'Panduan integrasi untuk dashboard atau command center pemerintah.',
            ],
        ],
    ],

    'documentation_url' => env('B2G_DOCS_URL'),
];