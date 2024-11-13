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
            <a href="#soirees-list" style="padding: 12px 30px; background-color: #f1590a; color: white; text-decoration: none; border-radius: 5px; font-size: 1rem; font-weight: bold; transition: background-color 0.3s;">
                Découvrez toutes nos soirées !
            </a>
        </div>
        HTML;

        // Rendu des soirées
        $output .= '<h1 style="text-align: center; font-size: 2.5rem; margin-bottom: 30px;">Découvrez toutes nos soirées</h1>';
        $output .= '<div class="soirees-list" id="soirees-list" style="display: flex; flex-wrap: wrap; justify-content: space-between; gap: 20px;">';

        foreach ($soirees as $soiree) {
            $renderer = new SoireeRenderer($soiree);
            $output .= $renderer->render(Renderer::COMPACT);
        }

        // Fermeture de la liste des soirées
        $output .= '</div>';

        return <<<END
            <h1><strong>Bienvenue au Festival NRV !<strong></h1>
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