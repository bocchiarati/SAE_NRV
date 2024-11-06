<?php

namespace iutnc\deefy\render;

use iutnc\deefy\audio\lists\AudioList;
use iutnc\deefy\audio\tracks\AlbumTrack;
use iutnc\deefy\render\RendererFactory;

class AudioListRenderer implements Renderer
{

    public AudioList $list;

    public function __construct(AudioList $list)
    {
        $this->list = $list;
    }

    public function render(int $selector): string
    {
        $chaine = '
        <div class="audio-list">
              <h2> Liste audio : ' . $this->list->nom . ' </h2> ';
        foreach ($this->list as $piste) {
            $render = RendererFactory::getRenderer($piste);
            $chaine .= $render->render(Renderer::COMPACT);
        }

        $chaine .= 'Nombre de piste :  ' . $this->list->nbPiste . '<br>';
        $chaine .= 'Duree totale :  ' . $this->convertirEnHeureMinuteSeconde($this->list->dureeTotale);
        $chaine .= '</div>';
        return $chaine;
    }

    private function convertirEnHeureMinuteSeconde($secondes) {
        $heures = floor($secondes / 3600);
        $minutes = floor(($secondes % 3600) / 60);
        $secondesRestantes = $secondes % 60;

        if ($heures != 0)
            return $heures."h ".$minutes."min ".$secondesRestantes."sec";
        else if($minutes != 0)
            return $minutes."min ".$secondesRestantes."sec";
        else
            return $secondes."sec ";
    }
}