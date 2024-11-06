<?php

namespace iutnc\nrv\render;

use iutnc\nrv\programme\ListSpectacle;

class ListSpectacleRenderer implements Renderer
{
    private ListSpectacle $list;

    public function __construct(ListSpectacle $l)
    {
        $this->list = $l;
    }

    public function render(int $selector): string
    {
        $chaine = '
        <div>
        <h2> Liste audio : ' . $this->list->nom . ' </h2> ';
        foreach ($this->list as $spec) {
            $render = new SpectacleRenderer($spec);
            $chaine .= $render->render(Renderer::COMPACT);
        }
        $chaine .= '</div>';
        return $chaine;
    }
}