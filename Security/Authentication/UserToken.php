<?php

namespace Caxy\AppEngine\Bridge\Security\Authentication;

use google\appengine\api\users\User;
use google\appengine\api\users\UserService;
use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

class UserToken extends AbstractToken
{
    public function __construct($roles = array())
    {
        if (UserService::isCurrentUserAdmin() && !in_array('ROLE_SUPER_ADMIN', $roles)) {
            $roles[] = 'ROLE_SUPER_ADMIN';
        }
        $this->setUser(UserService::getCurrentUser());
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
