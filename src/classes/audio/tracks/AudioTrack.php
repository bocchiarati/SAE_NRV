<?php

namespace iutnc\deefy\audio\tracks;

use iutnc\deefy\exception\InvalidPropertyNameException;
use iutnc\deefy\exception\InvalidPropertyValueException;
use ReflectionClass;

abstract class AudioTrack
{
    //Mettre uniquement des genres en constante !
    const FUNK = 1;
    const POLKA = 2;
    const CLASSIC_ROCK = 3;
    const COUNTRY = 4;
    const DOCUMENTAIRE = 5;

    const POP = 6;

    const RAP = 7;

    const CLASSIC_MUSIC = 8;

    protected ?int $id;
    protected string $titre;
    protected ?int $genre;
    protected ?float $duree;
    protected string $nomFichier;

    public function __construct(string $titre, float $duree ,string $chemin, int $genre)
    {
        $this->titre = $titre;
        $this->nomFichier = $chemin;
        $this->duree = $duree;
        $this->genre = $genre;
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

    public function setID(int $idTrack): void
    {
        $this->id = $idTrack;
    }

    /**
     * @throws InvalidPropertyValueException
     */
    public function setDuree(int $valeur): void
    {
        if ($valeur < 0) {
            throw new InvalidPropertyValueException("Durée inférieure à 0");
        } else {
            $this->duree = $valeur;
        }
    }

    public function __toString(): string
    {
        // Crée une copie des propriétés de l'objet
        $properties = get_object_vars($this);
        $properties['genre'] = $this->getGenreAsString($this->genre);

        // Retourne les propriétés sous forme de chaîne JSON
        return json_encode($properties);
    }

    public static function getGenreAsString(int $genre): string
    {
        // Utilisation de ReflectionClass pour obtenir les constantes de la classe
        $reflection = new ReflectionClass(__CLASS__);
        $constants = array_flip($reflection->getConstants());

        // Retourne le nom de la constante si elle existe
        return $constants[$genre] ?? 'Genre inconnu';
    }

    public static function getGenres(): array
    {
        $reflection = new ReflectionClass(__CLASS__);
        return $reflection->getConstants();
    }
}