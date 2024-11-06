<?php

namespace iutnc\nrv\auth;

use iutnc\nrv\repository\DeefyRepository;

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
     * @param int $playlistId
     * @return bool check si l'user est bien l'owner de la playlist
     */
    public function checkPlaylistOwner(int $playlistId): bool {
        $pdo=DeefyRepository::getInstance();
        return $this->authenticated_user->id === $pdo->findOwnerPlaylist($playlistId) || $this->authenticated_user->role >= User::ADMIN_USER;
    }
}