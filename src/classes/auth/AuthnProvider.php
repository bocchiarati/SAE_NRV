<?php

namespace iutnc\nrv\auth;

use iutnc\nrv\exception\AuthException;
use iutnc\nrv\repository\DeefyRepository;

class AuthnProvider
{
    /**
     * Connecte un user en session
     * @param string $email
     * @param string $passwd2check
     * @throws AuthException
     */
    public static function signin(string $email, string $passwd2check): void {

        $pdo = DeefyRepository::getInstance();
        $user = $pdo->getUser($email);

        if (!password_verify($passwd2check, $user->pass))
            throw new AuthException("Auth error : invalid credentials");

        $_SESSION['user'] = serialize($user);
    }

    /**
     * Enregistre un nouvel user
     * @param string $email
     * @param string $pass
     * @throws AuthException
     */
    public static function register(string $email, string $pass): void {
        if (! filter_var($email, FILTER_VALIDATE_EMAIL))
            throw new AuthException(" error : invalid user email");

        $hash = password_hash($pass, PASSWORD_DEFAULT, ['cost'=>12]);
        $pdo = DeefyRepository::getInstance();
        $user = $pdo->saveUser($email,$hash);

        $_SESSION['user'] = serialize($user);
    }

    /**
     * @return User en session
     * @throws AuthException
     */
    public static function getSignedInUser( ): User {
        if ( !isset($_SESSION['user']))
            throw new AuthException("Auth error : not signed in");

        return unserialize($_SESSION['user'] ) ;
    }

    /**
     * Deconnecte l'user en session
     */
    public static function signout(){
        unset($_SESSION['user']);
    }
}