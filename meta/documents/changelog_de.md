# Changelog

## 0.4.1 (10.11.2026)
- Box „Nicht gefunden?“: Text „Schreiben Sie uns, wir helfen gern weiter.“ (Satz zur Auftragsnummer gestrichen, im Kontaktformular ist sie freiwillig).
- Handy: Knopf „Kontakt aufnehmen“ war weiß auf weiß (Regel der Themenliste griff mit), jetzt dunkelblau. Die Box erscheint am Handy nur nach einer Suche, unter den Treffern.

## 0.4.0 (10.11.2026)
- FAQ-Seite: Abstand zum Menü, Breite wie der Shop (1300 px).
- Kontaktwege oben in der Box: Chat (nur wenn Tawk geladen ist, öffnet den Chat), E-Mail, Kontaktformular, Telefon.
- Neue Einstellung „E-Mail-Adresse (Anzeige)“.

## 0.3.0 (09.11.2026)
- Widgets geben nur noch den Aufruf `osvw_faq()` bzw. `osvw_kurzhilfe()` aus, die Texte werden erst beim Seitenaufruf gelesen. Bisher fror der ShopBuilder den Stand beim Speichern ein (alte Texte, alte Skripte, maskierte FAQPage-Daten, doppelter Footer).
- FAQPage-Daten kommen über den Container „OSV Wissen Suche“ am Seitenende, nur auf der FAQ-Seite.
- Neue Einstellung „Adresse der FAQ-Seite“ (für den Link der Kurzhilfe).
- Nach dem Update: Widget im ShopBuilder einmal entfernen, neu einsetzen, speichern.

## 0.2.0 (09.11.2026)
- CSS und Suchskript aus den Widgets in zwei Container verlegt: „OSV Wissen Stil“ (Ceres::Template.Style) und „OSV Wissen Suche“ (Ceres::Script.AfterScriptsLoaded). `<style>` und `<script>` im Widget brachten die Vue-Hydrierung durcheinander, auf der FAQ-Seite erschien der Footer doppelt.
- Suche hängt jetzt am document und greift auch nach einem Neuaufbau durch Vue.

## 0.1.2 (09.11.2026)
- FAQ-Suche: Skript ohne Zeichen, die Ceres maskiert. Ceres gibt Widget-Inhalte zusätzlich maskiert aus, die Kopie warf „Unexpected token '&'“. Doppelte Anbindung der Suche verhindert.

## 0.1.1 (09.11.2026)
- Suchwörter für die Widget-Suche im ShopBuilder (osv, faq, fragen, hilfe, kurzhilfe, kontakt, wissen).

## 0.1.0 (09.11.2026)
- Erste Fassung: Bausteine aus `daten/wissen.json` (35 Fragen, 13 Themen, Reihenfolge nach der Auswertung des Info-Postfachs 2024–2026).
- ShopBuilder-Widget „OSV Häufige Fragen“ (Suche, Themenliste, aufklappbare Antworten, Videos als Link zu YouTube, FAQPage-Daten).
- ShopBuilder-Widget „OSV Kurzhilfe“ für die Kontaktseite (Bausteine mit Ort `kontakt`).
- `WissenService`: einzige Abrufstelle für alle Seiten und Plugins (FAQ, Kontakt, Kategorie).
