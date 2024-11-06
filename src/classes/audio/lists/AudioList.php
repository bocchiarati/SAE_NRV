<?php

namespace iutnc\deefy\audio\lists;

use iutnc\deefy\exception\InvalidPropertyNameException;

abstract class AudioList implements \Iterator
{
    protected ?int $id;
    protected string $nom;
    protected int $nbPiste;
    protected float $dureeTotale;
    protected array $tab;

    private int $position = 0;

    /**
     * @param array $tab
     * @param string $nom
     */
    public function __construct(string $nom, array $tab=[])
    {
        $this->tab = $tab;
        $this->nom = $nom;
        $this->nbPiste = count($tab);
        $this->dureeTotale = 0;
        foreach ($tab as $piste){
            $this->dureeTotale += $piste->duree;
        }
    }

    /**
     * @throws InvalidPropertyNameException
     */
    public function __get(string $attribut): mixed
    {
        if (property_exists($this, $attribut))
            return $this->$attribut;
        throw new InvalidPropertyNameException(" $attribut : invalide propriete");
    }

    public function setID(int $idPlaylist): void
    {
        $this->id = $idPlaylist;
    }

    public function __toString(): string
    {
        return json_encode(get_object_vars($this));
    }

    /**
     * Return the current element
     * @link https://php.net/manual/en/iterator.current.php
     * @return TValue Can return any type.
     */
    #[\Override] public function current(): mixed
    {
        return $this->tab[$this->position];
    }

    /**
     * Move forward to next element
     * @link https://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    #[\Override] public function next(): void
    {
        ++$this->position;
    }

    /**
     * Return the key of the current element
     * @link https://php.net/manual/en/iterator.key.php
     * @return TKey|null TKey on success, or null on failure.
     */
    #[\Override] public function key(): mixed
    {
        return $this->position;
    }

    /**
     * Checks if current position is valid
     * @link https://php.net/manual/en/iterator.valid.php
     * @return bool The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */
    #[\Override] public function valid(): bool
    {
        return isset($this->tab[$this->position]);
    }

    /**
     * Rewind the Iterator to the first element
     * @link https://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    #[\Override] public function rewind(): void
    {
        $this->position = 0;
    }
}