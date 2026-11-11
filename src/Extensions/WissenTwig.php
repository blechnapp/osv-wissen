<?php

namespace OSVWissen\Extensions;

use OSVWissen\Services\WissenService;
use Plenty\Plugin\ConfigRepository;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Templates\Extensions\Twig_Extension;
use Plenty\Plugin\Templates\Factories\TwigFactory;
use Plenty\Plugin\Templates\Twig;

/**
 * Twig-Funktionen osvw_faq(), osvw_kurzhilfe(), osvw_videos() und osvw_ratgeber(id).
 * Der ShopBuilder speichert die Ausgabe eines Widgets beim Speichern als Twig-Vorlage. Die Widgets geben deshalb
 * nur den Aufruf dieser Funktionen aus, die Texte werden erst beim Seitenaufruf gelesen. So wirken neue Texte
 * und abgelaufene Bausteine (bis) ohne erneutes Speichern im ShopBuilder.
 */
class WissenTwig extends Twig_Extension
{
    /** @var string FAQPage-Daten der aktuellen Seite, gibt der Container „OSV Wissen Suche“ am Seitenende aus. */
    public static $schema = '';

    private $twig;

    public function __construct(TwigFactory $twig)
    {
        $this->twig = $twig;
    }

    public function getName(): string
    {
        return 'OSVWissen_Extension_WissenTwig';
    }

    public function getFunctions(): array
    {
        return [
            $this->twig->createSimpleFunction('osvw_faq', [$this, 'faq'], ['is_safe' => ['html']]),
            $this->twig->createSimpleFunction('osvw_kurzhilfe', [$this, 'kurzhilfe'], ['is_safe' => ['html']]),
            $this->twig->createSimpleFunction('osvw_videos', [$this, 'videos'], ['is_safe' => ['html']]),
            $this->twig->createSimpleFunction('osvw_ratgeber', [$this, 'ratgeber'], ['is_safe' => ['html']]),
        ];
    }

    public function getFilters(): array
    {
        return [];
    }

    public function getGlobals(): array
    {
        return [];
    }

    public function faq(): string
    {
        /** @var WissenService $wissen */
        $wissen = pluginApp(WissenService::class);
        /** @var ConfigRepository $config */
        $config = pluginApp(ConfigRepository::class);
        $themen = $wissen->faq();
        $schema = (string)$config->get('OSVWissen.schema', 'true');
        self::$schema = ($schema === 'false' || $schema === '0') ? '' : $wissen->schemaJson($themen);
        return pluginApp(Twig::class)->render('OSVWissen::Inhalt.Faq', [
            'themen'     => $themen,
            'kontaktUrl' => (string)$config->get('OSVWissen.kontaktUrl', '/kontakt/'),
            'telefon'    => (string)$config->get('OSVWissen.telefon', '+49 37362 774-0'),
            'email'      => (string)$config->get('OSVWissen.email', 'info@original-seiffener-volkskunst.de'),
            'zeiten'     => (string)$config->get('OSVWissen.servicezeiten', 'Mo–Fr 10–16 Uhr'),
            'videoKanal' => (string)$config->get('OSVWissen.videoKanal', ''),
        ]);
    }

    public function kurzhilfe(): string
    {
        /** @var WissenService $wissen */
        $wissen = pluginApp(WissenService::class);
        /** @var ConfigRepository $config */
        $config = pluginApp(ConfigRepository::class);
        $fragen = $wissen->kontakt();
        if (!count($fragen)) {
            return '';
        }
        return pluginApp(Twig::class)->render('OSVWissen::Inhalt.Kurzhilfe', [
            'fragen' => $fragen,
            'faqUrl' => (string)$config->get('OSVWissen.faqUrl', '/faq/'),
        ]);
    }

    public function videos(): string
    {
        /** @var WissenService $wissen */
        $wissen = pluginApp(WissenService::class);
        /** @var ConfigRepository $config */
        $config = pluginApp(ConfigRepository::class);
        return pluginApp(Twig::class)->render('OSVWissen::Inhalt.Videos', [
            'videos'      => $wissen->videos(),
            'kanal'       => (string)$config->get('OSVWissen.videoKanal', ''),
            'zeiten'      => (string)$config->get('OSVWissen.servicezeiten', 'Mo–Fr 10–16 Uhr'),
            'telefon'     => (string)$config->get('OSVWissen.telefon', '+49 37362 774-0'),
            'faqUrl'      => (string)$config->get('OSVWissen.faqUrl', '/faq/'),
            'ratgeberUrl' => (string)$config->get('OSVWissen.ratgeberUrl', ''),
        ]);
    }

    /** Übersicht „Gut zu wissen“ (id = uebersicht) oder ein einzelner Ratgeber. */
    public function ratgeber($id = 'uebersicht'): string
    {
        /** @var WissenService $wissen */
        $wissen = pluginApp(WissenService::class);
        /** @var ConfigRepository $config */
        $config = pluginApp(ConfigRepository::class);
        $basis = (string)$config->get('OSVWissen.ratgeberUrl', '/ratgeber/');
        $daten = [
            'basis'     => $basis === '' ? '/ratgeber/' : $basis,
            'liste'     => $wissen->ratgeberListe(),
            'zeiten'    => (string)$config->get('OSVWissen.servicezeiten', 'Mo–Fr 10–16 Uhr'),
            'telefon'   => (string)$config->get('OSVWissen.telefon', '+49 37362 774-0'),
            'faqUrl'    => (string)$config->get('OSVWissen.faqUrl', '/faq/'),
            'videosUrl' => (string)$config->get('OSVWissen.videosUrl', '/videos/'),
        ];
        if ((string)$id === 'uebersicht') {
            $id = $this->ratgeberAusAdresse();
        }
        if ((string)$id === 'uebersicht') {
            return pluginApp(Twig::class)->render('OSVWissen::Inhalt.RatgeberUebersicht', $daten);
        }
        $r = $wissen->ratgeber((string)$id);
        if ($r === null) {
            return '';
        }
        $daten['r'] = $r;
        return pluginApp(Twig::class)->render('OSVWissen::Inhalt.Ratgeber', $daten);
    }

    /** Letzter Teil der Adresse (/ratgeber/pyramide-dreht-nicht/), wenn es ein Ratgeber dieses Namens gibt, sonst uebersicht. So zeigt die Unterseite ihren Ratgeber auch dann, wenn im Widget nichts ausgewählt ist. */
    private function ratgeberAusAdresse(): string
    {
        $pfad = (string)pluginApp(Request::class)->getRequestUri();
        $pfad = explode('?', $pfad)[0];
        $teile = array_values(array_filter(explode('/', $pfad), 'strlen'));
        $letzter = count($teile) > 0 ? strtolower($teile[count($teile) - 1]) : '';
        if ($letzter !== '' && pluginApp(WissenService::class)->ratgeber($letzter) !== null) {
            return $letzter;
        }
        return 'uebersicht';
    }
}
