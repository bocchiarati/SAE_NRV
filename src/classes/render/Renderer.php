<?php

namespace iutnc\nrv\render;

interface Renderer
{
    const COMPACT = 1;
    const LONG = 2;
    const REPERTOIRE_IMAGE = "../image/";
    const REPERTOIRE_EXTRAITS = "../extraits/";
    public function render(int $selector): string;
}