<?php

namespace iutnc\nrv\action;

use iutnc\nrv\render\Renderer;
use iutnc\nrv\render\SoireeRenderer;
use iutnc\nrv\repository\NrvRepository;

class ActionDefaut extends Action
{

    public function executeGet(): string
    {

        $pdo = NrvRepository::getInstance();
        $soirees = $pdo->findAllSoireeWithSpectacle();

        // Section Promotionnelle
        $output = <<<HTML
        <div class="promo-section" style="padding: 40px 20px; text-align: center; border-radius: 8px; margin-bottom: 30px;">
            <h2 style="font-size: 2.5rem; color: #ff8c00; margin-bottom: 20px;">Ne Manquez Pas Nos Prochains Événements !</h2>
            <p style="font-size: 1.2rem; margin-bottom: 20px;">Des spectacles exceptionnels, des invités spéciaux, et une ambiance incroyable vous attendent !</p>
            <a href="#soirees-list" class="btn btn-outline-warning btn-orange p-3" style="text-decoration: none; border-radius: 5px; font-size: 1rem; font-weight: bold;">
                Découvrez toutes nos soirées !
            </a>
        </div>
        HTML;

        // Rendu des soirées
        $output .= '<h1 style="text-align: center; font-size: 2.5rem; margin-bottom: 30px;">Découvrez toutes nos soirées</h1>';
        $output .= '<div class="container" id="soirees-list" style="display: flex; flex-wrap: wrap; justify-content: space-between; gap: 20px;">
                    <div class="row row-cols-2">';

        foreach ($soirees as $soiree) {
            $renderer = new SoireeRenderer($soiree);
            $output .= $renderer->render(Renderer::COMPACT);
        }

        // Fermeture de la liste des soirées
        $output .= '</div></div>';

        return <<<END
            <h1 class="mt-4"><strong>Bienvenue au Festival NRV !<strong></h1>
            <div>
                {$output}
            </div>
        END;
    }

    public function executePost(): string
    {
        return "Aucune action selectionnée ou action invalide";
    }
}