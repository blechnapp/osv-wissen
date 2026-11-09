<?php

namespace OSVWissen\Widgets;

use Ceres\Widgets\Helper\BaseWidget;
use Ceres\Widgets\Helper\Factories\WidgetDataFactory;
use Ceres\Widgets\Helper\WidgetCategories;
use Ceres\Widgets\Helper\Factories\WidgetSettingsFactory;
use Ceres\Widgets\Helper\WidgetTypes;
use OSVWissen\Services\WissenService;
use Plenty\Plugin\ConfigRepository;

class FaqWidget extends BaseWidget
{
    protected $template = 'OSVWissen::Widgets.Faq';

    public function getData()
    {
        return WidgetDataFactory::make('OSVWissen::FaqWidget')
            ->withLabel('OSV Häufige Fragen')
            ->withPreviewImageUrl('/images/widgets/text.svg')
            ->withType(WidgetTypes::STATIC)
            ->withCategory(WidgetCategories::TEXT)
            ->withSearchKeyWords(["osv", "faq", "fragen", "hilfe", "wissen"])
            ->withPosition(100)
            ->toArray();
    }

    public function getSettings()
    {
        /** @var WidgetSettingsFactory $settings */
        $settings = pluginApp(WidgetSettingsFactory::class);
        $settings->createCustomClass();
        $settings->createSpacing();
        return $settings->toArray();
    }

    protected function getTemplateData($widgetSettings, $isPreview)
    {
        /** @var WissenService $wissen */
        $wissen = pluginApp(WissenService::class);
        /** @var ConfigRepository $config */
        $config = pluginApp(ConfigRepository::class);
        $themen = $wissen->faq();
        $schema = (string)$config->get('OSVWissen.schema', 'true');
        return [
            'themen'     => $themen,
            'anzahl'     => array_sum(array_map(function ($t) { return count($t['fragen']); }, $themen)),
            'schemaJson' => ($schema === 'false' || $schema === '0') ? '' : $wissen->schemaJson($themen),
            'kontaktUrl' => (string)$config->get('OSVWissen.kontaktUrl', '/kontakt/'),
            'telefon'    => (string)$config->get('OSVWissen.telefon', '+49 37362 774-0'),
            'zeiten'     => (string)$config->get('OSVWissen.servicezeiten', 'Mo–Fr 10–16 Uhr'),
            'videoKanal' => (string)$config->get('OSVWissen.videoKanal', ''),
        ];
    }
}
