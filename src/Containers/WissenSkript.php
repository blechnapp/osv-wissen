<?php

namespace OSVWissen\Containers;

use Plenty\Plugin\Templates\Twig;

/* Suche und Sprungmarken der FAQ-Seite (Ceres::Script.AfterScriptsLoaded). Ein <script> im Widget selbst bringt die Vue-Hydrierung durcheinander. */
class WissenSkript
{
    public function call(Twig $twig): string
    {
        return $twig->render('OSVWissen::Containers.WissenSkript');
    }
}
