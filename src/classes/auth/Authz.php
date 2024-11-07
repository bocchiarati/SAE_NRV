<?php

namespace iutnc\nrv\auth;

class Authz {

    private User $authenticated_user;

    public function __construct(User $user) {
        $this->authenticated_user = $user;
    }

    /**
     * @param int $required role requis
     * @return bool
     */
    public function checkRole(int $required): bool{
        return $this->authenticated_user->role >= $required;
    }

    /**
     * @return bool check si l'user est bien un organisateur
     */
    public function checkIsOrga(): bool {
        return $this->authenticated_user->role >= User::ORGANISATOR_USER;
    }
}