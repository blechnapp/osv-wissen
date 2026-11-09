<?php

namespace OSVWissen\Extensions;

use OSVWissen\Services\WissenService;
use Plenty\Plugin\ConfigRepository;
use Plenty\Plugin\Templates\Extensions\Twig_Extension;
use Plenty\Plugin\Templates\Factories\TwigFactory;
use Plenty\Plugin\Templates\Twig;

/**
 * Twig-Funktionen osvw_faq() und osvw_kurzhilfe().
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
}
