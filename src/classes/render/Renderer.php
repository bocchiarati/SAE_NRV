<?php

namespace iutnc\nrv\render;

interface Renderer
{
    const COMPACT = 1;
    const LONG = 2;

    public function render(int $selector): string;
}