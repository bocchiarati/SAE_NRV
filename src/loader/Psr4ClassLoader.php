<?php

namespace iutnc\deefy\loader;

class Psr4ClassLoader
{
    private string $prefix;
    private string $root;

    public function __construct(string $namespace, string $root)
    {
        $this->prefix = $namespace;
        $this->root = $root;
    }

    public function loadClass(string $classname): void{
        if(!str_starts_with($classname,$this->prefix))return;
        $chemin = str_replace($this->prefix,$this->root."/", $classname);
        $chemin = str_replace("\\","/", $chemin);
        $chemin .= '.php';
        if(file_exists($chemin)) {
            require_once($chemin);
            echo "Le fichier $chemin a correctement été chargé \n";
        }
    }

    public function register(): void
    {
        spl_autoload_register([$this, 'loadClass']);
    }
}