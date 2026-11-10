<?php

namespace OSVWissen\Widgets;

use Ceres\Widgets\Helper\BaseWidget;
use Ceres\Widgets\Helper\Factories\Settings\ValueListFactory;
use Ceres\Widgets\Helper\Factories\WidgetDataFactory;
use Ceres\Widgets\Helper\Factories\WidgetSettingsFactory;
use Ceres\Widgets\Helper\WidgetCategories;
use Ceres\Widgets\Helper\WidgetTypes;
use OSVWissen\Services\WissenService;

/** Ein Widget für die Übersicht „Gut zu wissen“ und für jeden einzelnen Ratgeber, Auswahl in den Widget-Einstellungen. */
class RatgeberWidget extends BaseWidget
{
    protected $template = 'OSVWissen::Widgets.Ratgeber';

    public function getData()
    {
        return WidgetDataFactory::make('OSVWissen::RatgeberWidget')
            ->withLabel('OSV Ratgeber')
            ->withPreviewImageUrl('/images/widgets/text.svg')
            ->withType(WidgetTypes::STATIC)
            ->withCategory(WidgetCategories::TEXT)
            ->withSearchKeyWords(["osv", "ratgeber", "gut zu wissen", "pflege", "anleitung", "wissen"])
            ->withPosition(100)
            ->toArray();
    }

    public function getSettings()
    {
        /** @var WidgetSettingsFactory $settings */
        $settings = pluginApp(WidgetSettingsFactory::class);
        $settings->createCustomClass();
        $liste = ValueListFactory::make()->addEntry('uebersicht', 'Übersicht „Gut zu wissen“');
        foreach (pluginApp(WissenService::class)->ratgeberListe() as $r) {
            $liste->addEntry($r['id'], $r['titel']);
        }
        $settings->createSelect('ansicht')
            ->withDefaultValue('uebersicht')
            ->withName('Was soll angezeigt werden?')
            ->withListBoxValues($liste->toArray());
        return $settings->toArray();
    }

    protected function getTemplateData($widgetSettings, $isPreview)
    {
        $wert = $widgetSettings['ansicht']['mobile'] ?? ($widgetSettings['ansicht'] ?? 'uebersicht');
        $id = preg_replace('/[^a-z0-9-]/', '', is_string($wert) ? $wert : 'uebersicht');
        return ['aufruf' => "{{ osvw_ratgeber('" . ($id === '' ? 'uebersicht' : $id) . "') }}"];
    }
}
