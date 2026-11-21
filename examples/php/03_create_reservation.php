<?php

declare(strict_types=1);

/**
 * Cartile REST API v1 — Idempotent Web Ön Rezervasyon Oluşturma (POST /reservations)
 */

$baseUrl = rtrim((string) (getenv('CARTILE_BASE_URL') ?: 'https://demo.cartile.com.tr/api/v1'), '/');
$apiKey = (string) (getenv('CARTILE_API_KEY') ?: '');

if ($apiKey === '') {
    fwrite(STDERR, "Hata: CARTILE_API_KEY ortam değişkenini ayarlayın.\n");
    exit(1);
}

$payload = [
    'plate' => '34 CRT 418',
    'starts_at' => date('Y-m-d\T10:00:00+03:00', strtotime('+3 days')),
    'planned_return_at' => date('Y-m-d\T10:00:00+03:00', strtotime('+7 days')),
    'external_reference' => 'WEB-2026-9041',
    'pickup_location' => 'Merkez Ofis',
    'return_location' => 'Merkez Ofis',
    'notes' => 'Web sitesi üzerinden otomatik ön rezervasyon talebi.',
    'customer' => [
        'first_name' => 'Ahmet',
        'last_name' => 'Yılmaz',
        'phone' => '0532 555 01 23',
        'email' => 'ahmet@ornek.com',
        'identity_type' => 'tc',
        'identity_number' => '12345678901',
        'license_number' => 'EHL-849201',
    ],
];

$ch = curl_init($baseUrl . '/reservations');
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 20,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: application/json',
        'Accept: application/json',
    ],
]);

$body = curl_exec($ch);
$httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr = curl_error($ch);
curl_close($ch);

if ($body === false) {
    fwrite(STDERR, "Bağlantı hatası: {$curlErr}\n");
    exit(1);
}

$data = json_decode($body, true);
echo "HTTP Durumu: {$httpCode}\n";
echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n";
