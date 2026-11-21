"""Cartile REST API v1 — Müsait Araçları Listeleme (GET /available-vehicles)."""

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

start_date = (date.today() + timedelta(days=2)).isoformat()
end_date = (date.today() + timedelta(days=5)).isoformat()

resp = requests.get(
    f"{base_url}/available-vehicles",
    params={
        "start": start_date,
        "end": end_date,
        "transmission": "otomatik",
    },
    headers={
        "Authorization": f"Bearer {api_key}",
        "Accept": "application/json",
    },
    timeout=15,
)

print("HTTP Durumu:", resp.status_code)
print(json.dumps(resp.json(), ensure_ascii=False, indent=2))
