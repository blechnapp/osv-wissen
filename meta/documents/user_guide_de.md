# OSV Wissen

Alle Hilfetexte des Shops aus **einer** Quelle. Kein Text steht doppelt.

## Einrichtung
1. Plugin im Plugin-Set installieren und bereitstellen (erst Set 75, dann Set 72).
2. Container-Verknüpfungen im Plugin-Set: „OSV Wissen Stil“ → Ceres::Template.Style, „OSV Wissen Suche“ → Ceres::Script.AfterScriptsLoaded. Ohne die beiden fehlen Gestaltung und Suche.
3. ShopBuilder → neue Inhaltskategorie „Häufige Fragen“ mit der Adresse `/faq/`, Widget **OSV Häufige Fragen** hineinziehen, speichern.
4. Kontaktseite: Widget **OSV Kurzhilfe** oberhalb des Formulars einfügen.

## Bausteine
`daten/wissen.json`, je Baustein:
- `id`, `thema`, `frage`, `absaetze` (Absatz; `- ` am Anfang = Listenpunkt; reine Adresse = Link)
- `video` (optional): `youtube` (ID) und `titel`
- `orte`: `faq`, `kontakt`, `artikel`
- `kategorien`: Plenty-Kategorie-IDs (für `artikel`)
- `bis` (optional): letzter Tag der Anzeige, JJJJ-MM-TT

Nach jeder Änderung: `python3 werkzeug/erzeugen.py` (prüft und schreibt `src/Daten/Bausteine.php`), Version in `plugin.json` hochzählen, Changelog, bereitstellen.

## Für andere Plugins
`OSVWissen\Services\WissenService`: `faq()`, `kontakt()`, `fuerKategorien(array $ids)`. Abgelaufene Bausteine (`bis`) fallen überall heraus.
Später kommt die Pflegemaske (PWA-Shop); dann ändert sich nur, woher der Service liest.

## Wie die Texte in den Shop kommen
Das Widget speichert nur den Aufruf `osvw_faq()` (bzw. `osvw_kurzhilfe()`). Die Texte liest das Plugin bei jedem Seitenaufruf aus `daten/wissen.json`. Neue Texte wirken nach dem Bereitstellen, ohne den ShopBuilder anzufassen.
