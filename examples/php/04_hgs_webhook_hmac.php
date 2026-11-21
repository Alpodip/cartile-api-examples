<?php

declare(strict_types=1);

/**
 * Cartile REST API v1 — HGS Webhook HMAC-SHA256 İmzalama ve Gönderim (POST /hgs/webhook)
 *
 * İmza ham JSON gövdesi üzerinden SHA-256 HMAC (küçük harf hex) olarak üretilir ve
 * "Webhook-Signature" başlığıyla gönderilir.
 */

function signCartileWebhook(string $rawBody, string $secret): string
{
    return hash_hmac('sha256', $rawBody, $secret);
}

function verifyCartileWebhook(string $rawBody, string $receivedSignature, string $secret): bool
{
    $expected = signCartileWebhook($rawBody, $secret);

    return hash_equals($expected, strtolower(trim($receivedSignature)));
}

$baseUrl = rtrim((string) (getenv('CARTILE_BASE_URL') ?: 'https://demo.cartile.com.tr/api/v1'), '/');
$secret = (string) (getenv('CARTILE_WEBHOOK_SECRET') ?: 'whsec_example_secret');

$rawBody = (string) json_encode([
    'event' => 'violation.detected',
    'plate' => '34CRT418',
    'violation_count' => 1,
    'amount' => 425.00,
    'operator' => 'KGM Otoyol',
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

$signature = signCartileWebhook($rawBody, $secret);
assert(verifyCartileWebhook($rawBody, $signature, $secret) === true);

if (getenv('CARTILE_SEND_WEBHOOK') !== '1') {
    echo "Üretilen HMAC-SHA256 Webhook-Signature: {$signature}\n";
    echo "Gerçek gönderim için CARTILE_SEND_WEBHOOK=1 CARTILE_WEBHOOK_SECRET=... ile çalıştırın.\n";
    exit(0);
}

$ch = curl_init($baseUrl . '/hgs/webhook');
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $rawBody,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 15,
    CURLOPT_HTTPHEADER => [
        'Webhook-Signature: ' . $signature,
        'Content-Type: application/json',
        'Accept: application/json',
    ],
]);

$body = curl_exec($ch);
$httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Durumu: {$httpCode}\n{$body}\n";
