/**
 * Cartile REST API v1 — HGS Webhook HMAC-SHA256 İmzalama ve Doğrulama (POST /hgs/webhook)
 */

import { createHmac, timingSafeEqual } from "node:crypto";

export function signCartileWebhook(rawBody, secret) {
  return createHmac("sha256", secret).update(rawBody, "utf8").digest("hex");
}

export function verifyCartileWebhook(rawBody, receivedSignature, secret) {
  const expectedHex = signCartileWebhook(rawBody, secret);
  const actualHex = String(receivedSignature || "").trim().toLowerCase();
  const a = Buffer.from(expectedHex, "utf8");
  const b = Buffer.from(actualHex, "utf8");
  return a.length === b.length && timingSafeEqual(a, b);
}

const baseUrl = (process.env.CARTILE_BASE_URL || "https://demo.cartile.com.tr/api/v1").replace(/\/$/, "");
const secret = process.env.CARTILE_WEBHOOK_SECRET || "whsec_example_secret";

const rawBody = JSON.stringify({
  event: "violation.detected",
  plate: "34CRT418",
  violation_count: 1,
  amount: 425.0,
  operator: "KGM Otoyol",
});

const signature = signCartileWebhook(rawBody, secret);
if (!verifyCartileWebhook(rawBody, signature, secret)) {
  throw new Error("HMAC doğrulama tutarsızlığı");
}

if (process.env.CARTILE_SEND_WEBHOOK !== "1") {
  console.log("Üretilen HMAC-SHA256 Webhook-Signature:", signature);
  console.log("Gerçek gönderim için CARTILE_SEND_WEBHOOK=1 CARTILE_WEBHOOK_SECRET=... ile çalıştırın.");
  process.exit(0);
}

const res = await fetch(`${baseUrl}/hgs/webhook`, {
  method: "POST",
  headers: {
    "Webhook-Signature": signature,
    "Content-Type": "application/json",
    Accept: "application/json",
  },
  body: rawBody,
});

console.log("HTTP Durumu:", res.status);
console.log(await res.text());
