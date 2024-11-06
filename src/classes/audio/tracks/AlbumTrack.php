<?php

namespace iutnc\deefy\audio\tracks;

use iutnc\deefy\exception\InvalidPropertyNameException;

class AlbumTrack extends AudioTrack
{
    private ?string $artiste;
    private ?int $annee;
    private ?string $album;
    private ?int $numPiste;

    public function __construct(string $titre, string $alb, float $duree, int $an, string $chemin, int $genre, string $artiste, int $num)
    {
        parent::__construct($titre, $duree, $chemin, $genre);
        $this->album = $alb;
        $this->annee = $an;
        $this->artiste = $artiste;
        $this->numPiste = $num;
    }

    /**
     * @throws InvalidPropertyNameException
     */
    public function __get(string $attribut): mixed
    {
        if ($attribut == "genre")
            return $this->getGenreAsString($this->$attribut);
        if ($attribut == "genreInt")
            return $this->genre;
        if (property_exists($this, $attribut))
            return $this->$attribut;
        throw new InvalidPropertyNameException(" $attribut : invalide propriete");
    }

    public function __toString(): string
    {
        return "AlbumTrack n°".$this->numPiste." : ".$this->titre." par ".$this->artiste." - ".self::getGenreAsString($this->genre);
    }
}