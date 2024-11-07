<?php

namespace iutnc\nrv\render;

use iutnc\nrv\programme\Spectacle;

class SpectacleRenderer implements Renderer {

    private Spectacle $spec;

    public function __construct(Spectacle $spec)
    {
        $this->spec = $spec;
    }

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

    private function renderCompact(): string
    {
        return <<<END
            <div>
                <img src='$this->spec->getImage()'>
            </div>
        END;
    }

    private function renderLong(): string
    {
        return <<<END
            <div>
            
            </div>
        END;
    }

}