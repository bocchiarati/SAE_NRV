<?php

namespace iutnc\nrv\auth;

use iutnc\nrv\exception\AuthException;
use iutnc\nrv\repository\NrvRepository;

class AuthnProvider
{
    /**
     * Connecte un user en session
     * @param string $email
     * @param string $passwd2check
     * @throws AuthException
     */
    public static function signin(string $email, string $passwd2check): void {

        $pdo = NrvRepository::getInstance();
        $user = $pdo->getUser($email);

        if (!password_verify($passwd2check, $user->pass)) {
            throw new AuthException("Auth error : invalid credentials");
        }

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

        // verifier la force du mot de passe
        if (!self::checkPasswordStrength($pass)) {
            throw new AuthException("Auth error: password is too weak");
        }

        $hash = password_hash($pass, PASSWORD_DEFAULT, ['cost'=>12]);
        $pdo = NrvRepository::getInstance();
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

    // verifier la force du mot de passe
    public static function checkPasswordStrength(string $pass, int $minimumLength = 10): bool
    {
        $length = (strlen($pass) >= $minimumLength);
        $digit = preg_match("#\d#", $pass);         // Au moins un chiffre
        $special = preg_match("#\W#", $pass);       // Au moins un caractere special
        $lower = preg_match("#[a-z]#", $pass);      // Au moins une lettre minuscule
        $upper = preg_match("#[A-Z]#", $pass);      // Au moins une lettre majuscule

        return $length && $digit && $special && $lower && $upper;
    }
}