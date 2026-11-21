"""Cartile REST API v1 — Idempotent Web Ön Rezervasyon Oluşturma (POST /reservations)."""

from datetime import date, timedelta
import json
import os
import sys
import requests

base_url = os.environ.get("CARTILE_BASE_URL", "https://demo.cartile.com.tr/api/v1").rstrip("/")
api_key = os.environ.get("CARTILE_API_KEY", "")

if not api_key:
    print("Hata: CARTILE_API_KEY ortam değişkenini ayarlayın.", file=sys.stderr)
    sys.exit(1)

start_iso = f"{(date.today() + timedelta(days=3)).isoformat()}T10:00:00+03:00"
end_iso = f"{(date.today() + timedelta(days=7)).isoformat()}T10:00:00+03:00"

payload = {
    "plate": "34 CRT 418",
    "starts_at": start_iso,
    "planned_return_at": end_iso,
    "external_reference": "WEB-2026-9041",
    "pickup_location": "Merkez Ofis",
    "return_location": "Merkez Ofis",
    "notes": "Web sitesinden idempotent ön rezervasyon.",
    "customer": {
        "first_name": "Ahmet",
        "last_name": "Yılmaz",
        "phone": "0532 555 01 23",
        "email": "ahmet@ornek.com",
        "identity_type": "tc",
        "identity_number": "12345678901",
        "license_number": "EHL-849201",
    },
}

resp = requests.post(
    f"{base_url}/reservations",
    headers={
        "Authorization": f"Bearer {api_key}",
        "Content-Type": "application/json",
        "Accept": "application/json",
    },
    json=payload,
    timeout=20,
)

print("HTTP Durumu:", resp.status_code)
print(json.dumps(resp.json(), ensure_ascii=False, indent=2))
