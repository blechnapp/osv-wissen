<?php

namespace OSVWissen\Providers;

use OSVWissen\Extensions\WissenTwig;
use OSVWissen\Widgets\FaqWidget;
use OSVWissen\Widgets\KurzhilfeWidget;
use OSVWissen\Widgets\RatgeberWidget;
use OSVWissen\Widgets\VideosWidget;
use Plenty\Modules\ShopBuilder\Contracts\ContentWidgetRepositoryContract;
use Plenty\Plugin\ServiceProvider;
use Plenty\Plugin\Templates\Twig;

class OSVWissenServiceProvider extends ServiceProvider
{
    public function register()
    {
    }

    public function boot(Twig $twig)
    {
        $twig->addExtension(WissenTwig::class);

        /** @var ContentWidgetRepositoryContract $widgetRepository */
        $widgetRepository = pluginApp(ContentWidgetRepositoryContract::class);
        $widgetRepository->registerWidget(FaqWidget::class);
        $widgetRepository->registerWidget(KurzhilfeWidget::class);
        $widgetRepository->registerWidget(VideosWidget::class);
        $widgetRepository->registerWidget(RatgeberWidget::class);
    }
}
