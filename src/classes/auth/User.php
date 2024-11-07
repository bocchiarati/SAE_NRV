<?php

namespace iutnc\nrv\auth;

use iutnc\nrv\exception\InvalidPropertyNameException;

class User
{
    const STANDARD_USER = 1;
    const ORGANISATOR_USER = 99;

    private int $id;
    private string $pass;
    private int $role;

    /**
     * @param int $id
     * @param string $pass
     */
    public function __construct(int $id, string $pass, int $role)
    {
        $this->id = $id;
        $this->pass = $pass;
        $this->role = $role;
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
}