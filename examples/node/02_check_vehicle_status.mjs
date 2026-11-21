/**
 * Cartile REST API v1 — Tekil Araç Müsaitlik & Tampon Gün Durumu (GET /vehicle-status)
 */

const baseUrl = (process.env.CARTILE_BASE_URL || "https://demo.cartile.com.tr/api/v1").replace(/\/$/, "");
const apiKey = process.env.CARTILE_API_KEY || "";
const plate = process.argv[2] || "34CRT418";

if (!apiKey) {
  console.error("Hata: CARTILE_API_KEY ortam değişkenini ayarlayın.");
  process.exit(1);
}

const url = `${baseUrl}/vehicle-status?${new URLSearchParams({ plate }).toString()}`;

const res = await fetch(url, {
  method: "GET",
  headers: {
    "X-Api-Key": apiKey,
    Accept: "application/json",
  },
});

const data = await res.json();
console.log("HTTP Durumu:", res.status);
console.log(JSON.stringify(data, null, 2));
