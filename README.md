# Cartile REST API v1 — Resmi Entegrasyon Örnekleri

[Cartile](https://cartile.com.tr/), oto kiralama (rent-a-car) işletmeleri için geliştirilmiş bulut tabanlı araç kiralama, filo, dijital sözleşme ve ön kasa yönetim platformudur.

Bu depo; rent-a-car web sitelerinin, rezervasyon motorlarının, WordPress sitelerinin ve HGS otomasyonlarının **Cartile REST API v1** uç noktalarına bağlanması için sıfır veya minimum bağımlılıkla çalışan örnek kodları ve Postman koleksiyonunu içerir.

*English overview:* Official integration examples (PHP, Node.js, Python, WordPress shortcode, and Postman collection) for the **Cartile REST API v1** — real-time fleet availability, idempotent online booking, and HMAC-SHA256 HGS webhook verification.

---

## İlgili Bağlantılar

- **Canlı API Dokümantasyonu:** https://cartile.com.tr/api-dokumantasyonu
- **OpenAPI 3.1.0 Şeması (JSON):** https://cartile.com.tr/openapi.json
- **Tek Tıkla Demo Paneli:** https://demo.cartile.com.tr/demo-giris
- **Ücretsiz A4 Araç Kiralama Sözleşmesi PDF Oluşturucu:** https://cartile.com.tr/arac-kiralama-sozlesmesi-ornegi
- **Ücretsiz 2026 Rent-A-Car Excel Takip Şablonu (.xlsx):** https://cartile.com.tr/rent-a-car-excel-takip-sablonu
- **YouTube Kanalı:** https://www.youtube.com/@cartilecomtr
- **LinkedIn Şirket Sayfası:** https://www.linkedin.com/company/cartile

---

## Depo Yapısı

```text
cartile-api-examples/
├── examples/
│   ├── php/                         # Saf PHP cURL + hash_hmac / hash_equals örnekleri
│   │   ├── 01_list_available_vehicles.php
│   │   ├── 02_check_vehicle_status.php
│   │   ├── 03_create_reservation.php
│   │   └── 04_hgs_webhook_hmac.php
│   ├── node/                        # Node.js 18+ yerleşik fetch + node:crypto örnekleri
│   │   ├── package.json
│   │   ├── 01_list_available_vehicles.mjs
│   │   ├── 02_check_vehicle_status.mjs
│   │   ├── 03_create_reservation.mjs
│   │   └── 04_hgs_webhook_hmac.mjs
│   ├── python/                      # Python requests + hmac örnekleri
│   │   ├── requirements.txt
│   │   ├── 01_list_available_vehicles.py
│   │   ├── 02_check_vehicle_status.py
│   │   ├── 03_create_reservation.py
│   │   └── 04_hgs_webhook_hmac.py
│   └── wordpress-shortcode/         # Tek dosyalık WordPress [cartile_araclar] eklentisi
│       └── cartile-availability-shortcode.php
└── postman/
    └── cartile-api-v1.postman_collection.json
```

---

## Kimlik Doğrulama (Authentication) ve Sunucu Adresleri

İki sunucu adresinden birini kullanabilirsiniz:

1. **İşletmeye Özel Alt Alan Adı:** `https://{isletme-kodu}.cartile.com.tr/api/v1`
2. **Merkezi API Geçidi:** `https://app.cartile.com.tr/api/v1` (kiracı `crt_live_...` anahtarından otomatik çözülür)

Tüm API isteklerinde API anahtarını HTTP başlığı olarak gönderin (`Authorization: Bearer crt_live_...` veya `X-Api-Key: crt_live_...`):

```bash
curl -X GET "https://demo.cartile.com.tr/api/v1/available-vehicles?start=2026-11-25&end=2026-11-28" \
  -H "Authorization: Bearer crt_live_replace_with_your_api_key" \
  -H "Accept: application/json"
```

> **Güvenlik Notu:** API anahtarınızı hiçbir zaman tarayıcı JavaScript kodunun (`frontend`) içine gömmeyin. Web sitenizin arka yüzü (PHP, Node.js, Python veya WordPress sunucu tarafı) üzerinden istek atın.

---

## Desteklenen Uç Noktalar (REST API v1)

| Metot | Yol | Açıklama |
| :--- | :--- | :--- |
| `GET` | `/available-vehicles` | Tarih aralığına göre müsait araçları, kampanyalı fiyatları ve toplam tutarı döner |
| `GET` | `/vehicle-status` | Plaka, ilan no, araç ID veya doğal dil (`q`) ile tekil araç müsaitlik & tampon gün bilgisini döner |
| `POST` | `/reservations` | `external_reference` ile idempotent (çift kayıt oluşturmayan), çakışma/kara liste kontrollü ön rezervasyon açar |
| `POST` | `/hgs/webhook` | `Webhook-Signature` (veya `X-HGS-Signature`) başlığındaki `HMAC-SHA256` imzasını doğrulayarak HGS bildirimlerini kabul eder |

---

## Hızlı Başlangıç

Ortam değişkenlerini hazırlayın:

```bash
cp .env.example .env
```

### 1. PHP (cURL uzantılı PHP 8.1+)

```bash
CARTILE_BASE_URL="https://demo.cartile.com.tr/api/v1" \
CARTILE_API_KEY="crt_live_your_key" \
php examples/php/01_list_available_vehicles.php
```

### 2. Node.js (Node 18+, ek paket gerekmez)

```bash
CARTILE_BASE_URL="https://demo.cartile.com.tr/api/v1" \
CARTILE_API_KEY="crt_live_your_key" \
node examples/node/01_list_available_vehicles.mjs
```

### 3. Python

```bash
pip install -r examples/python/requirements.txt
CARTILE_BASE_URL="https://demo.cartile.com.tr/api/v1" \
CARTILE_API_KEY="crt_live_your_key" \
python examples/python/01_list_available_vehicles.py
```

### 4. WordPress Kısa Kod (`[cartile_araclar]`)

`examples/wordpress-shortcode/cartile-availability-shortcode.php` dosyasını `/wp-content/plugins/` altına yükleyip etkinleştirin. `wp-config.php` içine şunları ekleyin:

```php
define('CARTILE_BASE_URL', 'https://isletmeniz.cartile.com.tr/api/v1');
define('CARTILE_API_KEY', 'crt_live_your_key');
```

Ardından herhangi bir sayfaya `[cartile_araclar days="3"]` kısa kodunu ekleyin. İsteğe bağlı olarak `[cartile_araclar credit="true"]` ile sayfa altında kaynak bağlantısı gösterebilirsiniz (varsayılan `false` gelir).

### 5. Postman / Insomnia İçe Aktarma

`postman/cartile-api-v1.postman_collection.json` dosyasını doğrudan Postman veya Insomnia içine aktararak tüm uç noktaları test edebilirsiniz.

---

## İdempotent Ön Rezervasyon ve HMAC-SHA256 Webhook Özeti

- **Çift Rezervasyon Koruması (`external_reference`):** Kendi sitenizin sipariş/talep numarasını (`ör. WEB-2026-9041`) `external_reference` olarak gönderin. Aynı referansla gelen tekrarlı istekler yeni kayıt açmaz; HTTP `200` ve `"idempotent_replay": true` ile mevcut rezervasyonu döner.
- **HGS Webhook İmzası:** `POST /hgs/webhook` uç noktası ham JSON gövdesi (`rawBody`) üzerinden `hash_hmac('sha256', rawBody, webhookSecret)` değerini üretip `Webhook-Signature` başlığı ile karşılaştırır (küçük harf hex digest).

---

## Lisans

Bu depo [MIT Lisansı](LICENSE) altında paylaşılmaktadır.
