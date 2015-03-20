<?php

namespace Caxy\AppEngine\Bridge\Security\Authentication;

use google\appengine\api\users\UserService;
use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

class UserToken extends AbstractToken
{
    public function __construct()
    {
        $roles = [];
        $user = UserService::getCurrentUser();
        if ($user) {
            $roles[] = 'ROLE_USER';
        }
        if (UserService::isCurrentUserAdmin()) {
            $roles[] = 'ROLE_ADMIN';
        }
        $this->setUser($user);
        $this->setAuthenticated(count($roles) > 0);
        parent::__construct($roles);
    }

    /**
     * Returns the user credentials.
     *
     * @return mixed The user credentials
     */
    public function getCredentials()
    {
        return '';
    }
}
