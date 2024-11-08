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
        $output = "<div class='spectacle-grid'>";
        foreach ($this->list as $spec) {
            $renderer = new SpectacleRenderer($spec);
            $output .= $renderer->render(Renderer::COMPACT);
        }
        $output .= '</div>';
        return $output;
    }
}