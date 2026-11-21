"""Cartile REST API v1 — HGS Webhook HMAC-SHA256 İmzalama ve Doğrulama (POST /hgs/webhook)."""

import hashlib
import hmac
import json
import os
import sys
import requests


def sign_cartile_webhook(raw_body: str, secret: str) -> str:
    return hmac.new(secret.encode("utf-8"), raw_body.encode("utf-8"), hashlib.sha256).hexdigest()


def verify_cartile_webhook(raw_body: str, received_signature: str, secret: str) -> bool:
    expected = sign_cartile_webhook(raw_body, secret)
    return hmac.compare_digest(expected, received_signature.strip().lower())


def main() -> int:
    base_url = os.environ.get("CARTILE_BASE_URL", "https://demo.cartile.com.tr/api/v1").rstrip("/")
    secret = os.environ.get("CARTILE_WEBHOOK_SECRET", "whsec_example_secret")

    raw_body = json.dumps(
        {
            "event": "violation.detected",
            "plate": "34CRT418",
            "violation_count": 1,
            "amount": 425.00,
            "operator": "KGM Otoyol",
        },
        ensure_ascii=False,
        separators=(",", ":"),
    )

    signature = sign_cartile_webhook(raw_body, secret)
    assert verify_cartile_webhook(raw_body, signature, secret)

    if os.environ.get("CARTILE_SEND_WEBHOOK") != "1":
        print("Üretilen HMAC-SHA256 Webhook-Signature:", signature)
        print("Gerçek gönderim için CARTILE_SEND_WEBHOOK=1 CARTILE_WEBHOOK_SECRET=... ile çalıştırın.")
        return 0

    resp = requests.post(
        f"{base_url}/hgs/webhook",
        data=raw_body.encode("utf-8"),
        headers={
            "Webhook-Signature": signature,
            "Content-Type": "application/json",
            "Accept": "application/json",
        },
        timeout=15,
    )
    print("HTTP Durumu:", resp.status_code)
    print(resp.text)
    return 0


if __name__ == "__main__":
    sys.exit(main())
