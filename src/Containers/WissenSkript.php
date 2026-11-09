<?php

namespace OSVWissen\Containers;

use OSVWissen\Extensions\WissenTwig;
use Plenty\Plugin\Templates\Twig;

/* Suche, Sprungmarken und FAQPage-Daten der FAQ-Seite (Ceres::Script.AfterScriptsLoaded). Ein <script> im Widget selbst bringt die Vue-Hydrierung durcheinander. */
class WissenSkript
{
    public function call(Twig $twig): string
    {
        $html = $twig->render('OSVWissen::Containers.WissenSkript');
        // FAQPage-Daten nur auf der Seite, auf der osvw_faq() lief (Container steht am Seitenende, nach dem Inhalt)
        if (WissenTwig::$schema !== '') {
            $html .= '<script type="application/ld+json">' . str_replace('</', '<\\/', WissenTwig::$schema) . '</script>';
        }
        return $html;
    }
}
