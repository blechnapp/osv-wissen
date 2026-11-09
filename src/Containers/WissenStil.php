<?php

namespace OSVWissen\Containers;

use Plenty\Plugin\Templates\Twig;

/* CSS der Widgets im Seitenkopf (Ceres::Template.Style). Ein <style> im Widget selbst bringt die Vue-Hydrierung durcheinander. */
class WissenStil
{
    public function call(Twig $twig): string
    {
        return $twig->render('OSVWissen::Partials.Stil');
    }
}
