<?php

namespace OSVWissen\Widgets;

use Ceres\Widgets\Helper\BaseWidget;
use Ceres\Widgets\Helper\Factories\WidgetDataFactory;
use Ceres\Widgets\Helper\WidgetCategories;
use Ceres\Widgets\Helper\Factories\WidgetSettingsFactory;
use Ceres\Widgets\Helper\WidgetTypes;

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

}
