<?php

namespace iutnc\deefy\render;

use iutnc\deefy\audio\tracks\PodcastTrack;

class PodcastTrackRenderer extends AudioTrackRenderer
{
    public PodcastTrack $podcast;

    public function __construct(PodcastTrack $pod)
    {
        $this->podcast = $pod;
    }

    //Méthode pour le rendu format court
    protected function renderCompact(): string
    {
        return '
        <div class="podcast compact">
            <ul>
                <li>Informations sur le podcast : <pre>' . $this->podcast->__toString() . '</pre></li>
                <li>
                    <audio controls>
                        <source src="' . self::REPERTOIRE_AUDIO . $this->podcast->nomFichier . '" type="audio/mpeg">
                    </audio>
                </li>
            </ul>
        </div>';
    }

    //Méthode pour le rendu format long
    protected function renderLong(): string
    {
        return '
        <div class="podcast long" style="width: 100%; height: 100vh; display: flex; align-items: center; justify-content: center;">
            <div>
                <h2>Infos sur le podcast :</h2>
                <pre>' . $this->podcast->__toString() . '</pre>
            </div>
            <audio controls style="width: 100%; max-width: 600px;">
                <source src="' . self::REPERTOIRE_AUDIO . $this->podcast->nomFichier . '" type="audio/mpeg">
            </audio>
        </div>';
    }
}