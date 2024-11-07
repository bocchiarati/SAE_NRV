<?php

namespace iutnc\nrv\render;

use iutnc\nrv\programme\ListSpectacle;

class ListSpectacleRenderer implements Renderer
{
    private ListSpectacle $list;

    public function __construct(ListSpectacle $list)
    {
        $this->list = $list;
    }

    public function render(int $selector): string
    {
        $output = "<div><h2>Liste des Spectacles</h2>";
        foreach ($this->list as $spec) {
            $renderer = new SpectacleRenderer($spec);
            $output .= $renderer->render($selector);
        }
        $output .= '</div>';
        return $output;
    }
}