/**
 * Cartile REST API v1 — Idempotent Web Ön Rezervasyon Oluşturma (POST /reservations)
 */

const baseUrl = (process.env.CARTILE_BASE_URL || "https://demo.cartile.com.tr/api/v1").replace(/\/$/, "");
const apiKey = process.env.CARTILE_API_KEY || "";

if (!apiKey) {
  console.error("Hata: CARTILE_API_KEY ortam değişkenini ayarlayın.");
  process.exit(1);
}

const now = Date.now();
const startIso = new Date(now + 3 * 86400_000).toISOString().slice(0, 10) + "T10:00:00+03:00";
const endIso = new Date(now + 7 * 86400_000).toISOString().slice(0, 10) + "T10:00:00+03:00";

const payload = {
  plate: "34 CRT 418",
  starts_at: startIso,
  planned_return_at: endIso,
  external_reference: "WEB-2026-9041",
  pickup_location: "Merkez Ofis",
  return_location: "Merkez Ofis",
  notes: "Web sitesinden idempotent ön rezervasyon.",
  customer: {
    first_name: "Ahmet",
    last_name: "Yılmaz",
    phone: "0532 555 01 23",
    email: "ahmet@ornek.com",
    identity_type: "tc",
    identity_number: "12345678901",
    license_number: "EHL-849201",
  },
};

const res = await fetch(`${baseUrl}/reservations`, {
  method: "POST",
  headers: {
    Authorization: `Bearer ${apiKey}`,
    "Content-Type": "application/json",
    Accept: "application/json",
  },
  body: JSON.stringify(payload),
});

const data = await res.json();
console.log("HTTP Durumu:", res.status);
console.log(JSON.stringify(data, null, 2));
