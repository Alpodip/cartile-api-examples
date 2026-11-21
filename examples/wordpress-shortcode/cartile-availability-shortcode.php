<?php

/**
 * Plugin Name: Cartile Rent-A-Car Canlı Araç Listesi
 * Plugin URI: https://cartile.com.tr/api-dokumantasyonu
 * Description: Cartile REST API v1 üzerinden web sitenizde müsait araçları sunucu tarafında güvenli olarak listeleyen [cartile_araclar] kısa kodu.
 * Version: 1.0.0
 * Author: Cartile
 * Author URI: https://cartile.com.tr/
 * License: MIT
 */

defined('ABSPATH') || exit;

add_shortcode('cartile_araclar', static function ($atts = []): string {
    $atts = shortcode_atts([
        'days' => 3,
        'transmission' => '',
        'brand' => '',
        'limit' => 12,
        'credit' => 'false',
    ], is_array($atts) ? $atts : [], 'cartile_araclar');

    $baseUrl = defined('CARTILE_BASE_URL')
        ? (string) CARTILE_BASE_URL
        : (string) get_option('cartile_base_url', 'https://demo.cartile.com.tr/api/v1');
    $apiKey = defined('CARTILE_API_KEY')
        ? (string) CARTILE_API_KEY
        : (string) get_option('cartile_api_key', '');

    $baseUrl = rtrim($baseUrl, '/');
    if ($apiKey === '' || $baseUrl === '') {
        return '<!-- Cartile API anahtarı yapılandırılmadı (CARTILE_API_KEY). -->';
    }

    $days = max(1, min(30, (int) $atts['days']));
    $limit = max(1, min(50, (int) $atts['limit']));
    $transmission = preg_replace('/[^a-z]/', '', strtolower((string) $atts['transmission'])) ?? '';
    $brand = sanitize_text_field((string) $atts['brand']);
    $showCredit = filter_var($atts['credit'], FILTER_VALIDATE_BOOLEAN);

    $start = gmdate('Y-m-d', strtotime('+1 day'));
    $end = gmdate('Y-m-d', strtotime('+' . (1 + $days) . ' days'));

    $queryArgs = [
        'start' => $start,
        'end' => $end,
    ];
    if ($transmission !== '') {
        $queryArgs['transmission'] = $transmission;
    }
    if ($brand !== '') {
        $queryArgs['brand'] = $brand;
    }

    $cacheKey = 'cartile_avail_' . md5($baseUrl . '|' . wp_json_encode($queryArgs));
    $payload = get_transient($cacheKey);

    if (! is_array($payload)) {
        $url = $baseUrl . '/available-vehicles?' . http_build_query($queryArgs);
        $response = wp_remote_get($url, [
            'timeout' => 12,
            'headers' => [
                'Authorization' => 'Bearer ' . $apiKey,
                'Accept' => 'application/json',
            ],
        ]);

        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
            return '<p class="cartile-notice">Araç müsaitlik listesi şu an alınamadı.</p>';
        }

        $decoded = json_decode((string) wp_remote_retrieve_body($response), true);
        if (! is_array($decoded) || empty($decoded['success'])) {
            return '<p class="cartile-notice">Uygun araç verisi bulunamadı.</p>';
        }

        $payload = $decoded;
        set_transient($cacheKey, $payload, 5 * MINUTE_IN_SECONDS);
    }

    $vehicles = isset($payload['vehicles']) && is_array($payload['vehicles'])
        ? array_slice($payload['vehicles'], 0, $limit)
        : [];

    if ($vehicles === []) {
        return '<p class="cartile-empty">Seçili tarih aralığında müsait araç bulunamadı.</p>';
    }

    $html = '<div class="cartile-fleet-grid" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:16px;">';
    foreach ($vehicles as $vehicle) {
        if (! is_array($vehicle)) {
            continue;
        }
        $title = trim((string) (($vehicle['brand'] ?? '') . ' ' . ($vehicle['model'] ?? '')));
        $year = isset($vehicle['year']) ? (string) (int) $vehicle['year'] : '';
        $fuel = (string) ($vehicle['fuel_type'] ?? '');
        $gear = (string) ($vehicle['transmission'] ?? '');
        $dailyRate = number_format((float) ($vehicle['daily_rate'] ?? 0), 0, ',', '.');
        $totalRate = number_format((float) ($vehicle['campaign_estimated_total'] ?? $vehicle['estimated_total'] ?? 0), 0, ',', '.');

        $html .= '<article class="cartile-vehicle-card" style="border:1px solid #e2e8f0;border-radius:10px;padding:16px;background:#ffffff;">';
        $html .= '<h3 style="margin:0 0 6px;font-size:17px;">' . esc_html($title) . '</h3>';
        $html .= '<p style="margin:0 0 10px;color:#475569;font-size:13px;">'
            . esc_html(implode(' · ', array_filter([$year, $fuel, $gear])))
            . '</p>';
        $html .= '<p style="margin:0;font-weight:700;font-size:15px;">'
            . esc_html($dailyRate) . ' TL / gün'
            . ' <span style="font-weight:400;color:#64748b;font-size:13px;">(' . esc_html((string) $days) . ' gün: ' . esc_html($totalRate) . ' TL)</span>'
            . '</p>';
        $html .= '</article>';
    }
    $html .= '</div>';

    if ($showCredit) {
        $html .= '<p class="cartile-credit" style="margin-top:10px;font-size:12px;color:#64748b;">'
            . 'Canlı filo verisi: <a href="' . esc_url('https://cartile.com.tr/') . '" target="_blank" rel="noopener noreferrer">Cartile Rent-A-Car Programı</a>'
            . '</p>';
    }

    return $html;
});
