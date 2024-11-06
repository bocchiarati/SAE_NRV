<?php

namespace iutnc\deefy\render;

use iutnc\deefy\audio\tracks\AlbumTrack;

class AlbumTrackRenderer extends AudioTrackRenderer
{
    public AlbumTrack $piste;

    public function __construct(AlbumTrack $track)
    {
        $this->piste = $track;
    }

    //Méthode pour le rendu format court
    protected function renderCompact(): string
    {
        return '
        <div class="audio-track compact">
            <ul id="track-render">
                <li>Informations de la piste : <pre>' . $this->piste->__toString() . '</pre></li>
                <li>
                    <audio controls id="audio-render">
                        <source src="' . self::REPERTOIRE_AUDIO . $this->piste->nomFichier . '" type="audio/mpeg">
                    </audio>
                </li>
            </ul>
            
            <style>
            #track-render {
                display:flex;
            }
            
            #audio-render {
                margin-left: 30px;
                width: 600px;
            }
            </style>
        </div>';
    }

    //Méthode pour le rendu format long
    protected function renderLong(): string
    {
        return '
        <div class="audio-track long" style="width: 100%; height: 100vh; display: flex; align-items: center; justify-content: center;">
            <div>
                <h2>Infos de la piste :</h2>
                <pre>' . $this->piste->__toString() . '</pre>
            </div>
            <audio controls style="width: 100%; max-width: 600px;">
                <source src="' . self::REPERTOIRE_AUDIO . $this->piste->nomFichier . '" type="audio/mpeg">
            </audio>
        </div>';
    }
}