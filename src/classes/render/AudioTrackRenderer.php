<?php

namespace iutnc\deefy\render;

abstract class AudioTrackRenderer implements Renderer
{
    public function render(int $selector): string
    {
        switch ($selector) {
            case self::COMPACT:
                $res = $this->renderCompact();
                break;
            case self::LONG:
                $res = $this->renderLong();
                break;
            default:
                $res = "Mode invalide";
                break;
        }
        return $res;
    }

    abstract protected function renderCompact();

    abstract protected function renderLong();
}