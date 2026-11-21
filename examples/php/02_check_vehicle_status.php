<?php

declare(strict_types=1);

/**
 * Cartile REST API v1 — Tekil Araç Müsaitlik & Tampon Gün Sorgulama (GET /vehicle-status)
 */

$baseUrl = rtrim((string) (getenv('CARTILE_BASE_URL') ?: 'https://demo.cartile.com.tr/api/v1'), '/');
$apiKey = (string) (getenv('CARTILE_API_KEY') ?: '');
$plate = $argv[1] ?? '34CRT418';

if ($apiKey === '') {
    fwrite(STDERR, "Hata: CARTILE_API_KEY ortam değişkenini ayarlayın.\n");
    exit(1);
}

$url = $baseUrl . '/vehicle-status?' . http_build_query(['plate' => $plate]);

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 15,
    CURLOPT_HTTPHEADER => [
        'X-Api-Key: ' . $apiKey,
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
