<?php

namespace iutnc\deefy\audio\tracks;

use iutnc\deefy\exception\InvalidPropertyNameException;

class PodcastTrack extends AudioTrack
{
    private ?string $auteur;
    private ?string $date;

    public function __construct(string $titre, string $chemin, string $auteur, string $date, int $duree, int $genre)
    {
        parent::__construct($titre, $duree, $chemin, $genre);
        $this->auteur = $auteur;
        $this->date = $date;
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
        return "Podcast du ".$this->date." : "." : ".$this->titre." par ".$this->auteur." - ".self::getGenreAsString($this->genre);
    }
}