<?php

namespace iutnc\deefy\render;

interface Renderer
{
    const COMPACT = 1;
    const LONG = 2;

    const REPERTOIRE_AUDIO = '../FichiersAudio/';

    public function render(int $selector): string;
}