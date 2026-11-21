/**
 * Cartile REST API v1 — Müsait Araçları Listeleme (GET /available-vehicles)
 * Gereksinim: Node.js 18+ (yerleşik fetch)
 */

const baseUrl = (process.env.CARTILE_BASE_URL || "https://demo.cartile.com.tr/api/v1").replace(/\/$/, "");
const apiKey = process.env.CARTILE_API_KEY || "";

if (!apiKey) {
  console.error("Hata: CARTILE_API_KEY ortam değişkenini ayarlayın.");
  process.exit(1);
}

const now = new Date();
const startDate = new Date(now.getTime() + 2 * 86400_000).toISOString().slice(0, 10);
const endDate = new Date(now.getTime() + 5 * 86400_000).toISOString().slice(0, 10);

const params = new URLSearchParams({
  start: startDate,
  end: endDate,
  transmission: "otomatik",
});

const res = await fetch(`${baseUrl}/available-vehicles?${params.toString()}`, {
  method: "GET",
  headers: {
    Authorization: `Bearer ${apiKey}`,
    Accept: "application/json",
  },
});

const data = await res.json();
console.log("HTTP Durumu:", res.status);
console.log(JSON.stringify(data, null, 2));
