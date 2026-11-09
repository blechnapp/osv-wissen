# Changelog

## 0.1.2 (09.11.2026)
- FAQ-Suche: Skript ohne Zeichen, die Ceres maskiert. Ceres gibt Widget-Inhalte zusätzlich maskiert aus, die Kopie warf „Unexpected token '&'“. Doppelte Anbindung der Suche verhindert.

## 0.1.1 (09.11.2026)
- Suchwörter für die Widget-Suche im ShopBuilder (osv, faq, fragen, hilfe, kurzhilfe, kontakt, wissen).

## 0.1.0 (09.11.2026)
- Erste Fassung: Bausteine aus `daten/wissen.json` (35 Fragen, 13 Themen, Reihenfolge nach der Auswertung des Info-Postfachs 2024–2026).
- ShopBuilder-Widget „OSV Häufige Fragen“ (Suche, Themenliste, aufklappbare Antworten, Videos als Link zu YouTube, FAQPage-Daten).
- ShopBuilder-Widget „OSV Kurzhilfe“ für die Kontaktseite (Bausteine mit Ort `kontakt`).
- `WissenService`: einzige Abrufstelle für alle Seiten und Plugins (FAQ, Kontakt, Kategorie).
