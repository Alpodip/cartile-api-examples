"""Cartile REST API v1 — Tekil Araç Müsaitlik & Tampon Gün Durumu (GET /vehicle-status)."""

import json
import os
import sys
import requests

base_url = os.environ.get("CARTILE_BASE_URL", "https://demo.cartile.com.tr/api/v1").rstrip("/")
api_key = os.environ.get("CARTILE_API_KEY", "")
plate = sys.argv[1] if len(sys.argv) > 1 else "34CRT418"

if not api_key:
    print("Hata: CARTILE_API_KEY ortam değişkenini ayarlayın.", file=sys.stderr)
    sys.exit(1)

resp = requests.get(
    f"{base_url}/vehicle-status",
    params={"plate": plate},
    headers={
        "X-Api-Key": api_key,
        "Accept": "application/json",
    },
    timeout=15,
)

print("HTTP Durumu:", resp.status_code)
print(json.dumps(resp.json(), ensure_ascii=False, indent=2))
