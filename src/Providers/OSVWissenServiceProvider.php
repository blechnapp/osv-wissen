<?php

namespace OSVWissen\Providers;

use OSVWissen\Widgets\FaqWidget;
use OSVWissen\Widgets\KurzhilfeWidget;
use Plenty\Modules\ShopBuilder\Contracts\ContentWidgetRepositoryContract;
use Plenty\Plugin\ServiceProvider;

class OSVWissenServiceProvider extends ServiceProvider
{
    public function register()
    {
    }

    public function boot()
    {
        /** @var ContentWidgetRepositoryContract $widgetRepository */
        $widgetRepository = pluginApp(ContentWidgetRepositoryContract::class);
        $widgetRepository->registerWidget(FaqWidget::class);
        $widgetRepository->registerWidget(KurzhilfeWidget::class);
    }
}
