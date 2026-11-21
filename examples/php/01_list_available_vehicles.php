<?php

declare(strict_types=1);

/**
 * Cartile REST API v1 — Tarih Aralığına Göre Müsait Araçları Listeleme (GET /available-vehicles)
 *
 * Kullanım:
 *   CARTILE_BASE_URL="https://demo.cartile.com.tr/api/v1" \
 *   CARTILE_API_KEY="crt_live_your_key" \
 *   php examples/php/01_list_available_vehicles.php
 */

$baseUrl = rtrim((string) (getenv('CARTILE_BASE_URL') ?: 'https://demo.cartile.com.tr/api/v1'), '/');
$apiKey = (string) (getenv('CARTILE_API_KEY') ?: '');

if ($apiKey === '') {
    fwrite(STDERR, "Hata: CARTILE_API_KEY ortam değişkenini ayarlayın.\n");
    exit(1);
}

$query = http_build_query([
    'start' => date('Y-m-d', strtotime('+2 days')),
    'end' => date('Y-m-d', strtotime('+5 days')),
    'transmission' => 'otomatik',
]);

$ch = curl_init($baseUrl . '/available-vehicles?' . $query);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 15,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $apiKey,
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
