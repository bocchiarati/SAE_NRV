<?php

namespace iutnc\deefy\render;

use iutnc\deefy\audio\tracks\AlbumTrack;
use iutnc\deefy\audio\tracks\AudioTrack;

class RendererFactory
{
    static function getRenderer(AudioTrack $track): Renderer
    {
        if($track instanceof AlbumTrack){
            $render = new AlbumTrackRenderer($track);
        }else{
            $render = new PodcastTrackRenderer($track);
        }
        return $render;
    }
}