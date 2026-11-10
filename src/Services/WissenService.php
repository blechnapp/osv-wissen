<?php

namespace OSVWissen\Services;

use OSVWissen\Daten\Bausteine;

/**
 * Einzige Abrufstelle für Hilfetexte. Seiten und andere Plugins lesen nur hierüber,
 * damit später nur diese Klasse auf die Pflegemaske umgestellt werden muss.
 */
class WissenService
{
    /** @var array|null */
    private $daten = null;

    private function daten(): array
    {
        if ($this->daten === null) {
            $this->daten = Bausteine::alle();
        }
        return $this->daten;
    }

    private function gueltig(array $b): bool
    {
        $bis = (string)($b['bis'] ?? '');
        return $bis === '' || date('Y-m-d') <= $bis;
    }

    private function fuerOrt(string $ort): array
    {
        $liste = [];
        foreach ($this->daten()['bausteine'] as $b) {
            if (in_array($ort, $b['orte'], true) && $this->gueltig($b)) {
                $liste[] = $this->aufbereiten($b);
            }
        }
        return $liste;
    }

    /** Absätze in Anzeigeblöcke: p, ul (aus Zeilen mit "- "), link (reine Adresse). */
    private function aufbereiten(array $b): array
    {
        $bloecke = [];
        foreach ($b['absaetze'] as $a) {
            $a = trim((string)$a);
            if ($a === '') {
                continue;
            }
            if (substr($a, 0, 2) === '- ') {
                $letzter = count($bloecke) - 1;
                if ($letzter >= 0 && $bloecke[$letzter]['typ'] === 'ul') {
                    $bloecke[$letzter]['punkte'][] = substr($a, 2);
                } else {
                    $bloecke[] = ['typ' => 'ul', 'punkte' => [substr($a, 2)]];
                }
            } elseif (preg_match('#^https?://\S+$#', $a)) {
                $bloecke[] = ['typ' => 'link', 'url' => $a, 'text' => preg_replace('#^https?://(www\.)?#', '', rtrim($a, '/'))];
            } else {
                $bloecke[] = ['typ' => 'p', 'text' => $a];
            }
        }
        $b['bloecke'] = $bloecke;
        $b['klartext'] = trim(implode(' ', array_map(function ($a) { return ltrim((string)$a, '- '); }, $b['absaetze'])));
        return $b;
    }

    /** FAQ: Themen in fester Reihenfolge, jedes mit seinen gültigen Bausteinen; leere Themen fallen weg. */
    public function faq(): array
    {
        $alle = $this->fuerOrt('faq');
        $themen = [];
        foreach ($this->daten()['themen'] as $t) {
            $fragen = array_values(array_filter($alle, function ($b) use ($t) { return $b['thema'] === $t['id']; }));
            if (count($fragen)) {
                $t['fragen'] = $fragen;
                $themen[] = $t;
            }
        }
        return $themen;
    }

    /** Videos in fester Reihenfolge (Vorschaubild liegt im Plugin unter images/video/<youtube>.webp). */
    public function videos(): array
    {
        return $this->daten()['videos'] ?? [];
    }

    private function woerter(string $text): int
    {
        $text = trim($text);
        return $text === '' ? 0 : count(preg_split('/\s+/u', $text));
    }

    /** Ratgeber für die Übersicht: id, titel, kurz, marke, bild, lesezeit. */
    public function ratgeberListe(): array
    {
        $liste = [];
        foreach ($this->daten()['ratgeber'] ?? [] as $r) {
            $voll = $this->ratgeber($r['id']);
            $liste[] = ['id' => $r['id'], 'titel' => $r['titel'], 'kurz' => $r['kurz'], 'marke' => $r['marke'],
                'bild' => $r['bild'], 'lesezeit' => $voll['lesezeit']];
        }
        return $liste;
    }

    /** Ein Ratgeber mit fertigen Abschnitten (Texte aus den Bausteinen), Video und Lesezeit; null, wenn es ihn nicht gibt. */
    public function ratgeber(string $id): ?array
    {
        $bausteine = [];
        foreach ($this->daten()['bausteine'] as $b) {
            $bausteine[$b['id']] = $b;
        }
        foreach ($this->daten()['ratgeber'] ?? [] as $r) {
            if ($r['id'] !== $id) {
                continue;
            }
            $woerter = $this->woerter((string)($r['einleitung'] ?? ''));
            $abschnitte = [];
            foreach ($r['abschnitte'] as $a) {
                $absaetze = isset($a['baustein']) ? ($bausteine[$a['baustein']]['absaetze'] ?? []) : ($a['absaetze'] ?? []);
                if (!empty($a['liste'])) {
                    $absaetze = array_map(function ($x) { return substr((string)$x, 0, 2) === '- ' || preg_match('#^https?://#', (string)$x) ? $x : '- ' . $x; }, $absaetze);
                }
                $fertig = $this->aufbereiten(['absaetze' => $absaetze]);
                $woerter += $this->woerter($fertig['klartext']);
                $abschnitte[] = ['titel' => $a['titel'], 'bloecke' => $fertig['bloecke']];
            }
            $r['abschnitte'] = $abschnitte;
            $r['lesezeit'] = max(1, (int)ceil($woerter / 180));
            $r['videoDaten'] = null;
            foreach ($this->videos() as $v) {
                if (($r['video'] ?? '') === $v['youtube']) {
                    $r['videoDaten'] = $v;
                }
            }
            return $r;
        }
        return null;
    }

    public function kontakt(): array
    {
        return $this->fuerOrt('kontakt');
    }

    /** Für die Artikelseite: Bausteine mit Ort "artikel", deren Kategorien eine der übergebenen treffen. */
    public function fuerKategorien(array $kategorieIds): array
    {
        $ids = array_map('intval', $kategorieIds);
        return array_values(array_filter($this->fuerOrt('artikel'), function ($b) use ($ids) {
            return count(array_intersect(array_map('intval', $b['kategorien'] ?? []), $ids)) > 0;
        }));
    }

    /** schema.org FAQPage als JSON-Text. */
    public function schemaJson(array $themen): string
    {
        $haupt = [];
        foreach ($themen as $t) {
            foreach ($t['fragen'] as $b) {
                $haupt[] = ['@type' => 'Question', 'name' => $b['frage'],
                    'acceptedAnswer' => ['@type' => 'Answer', 'text' => $b['klartext']]];
            }
        }
        return json_encode(['@context' => 'https://schema.org', '@type' => 'FAQPage', 'mainEntity' => $haupt],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
