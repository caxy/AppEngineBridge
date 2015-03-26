<?php

namespace Caxy\AppEngine\Bridge\Security\User;

use Symfony\Component\Security\Core\User\UserInterface;

class User implements UserInterface
{
    /**
     * @var \google\appengine\api\users\User
     */
    private $user;

    /**
     * @var bool
     */
    private $isAdmin;

    /**
     * @param \google\appengine\api\users\User $user
     * @param bool                             $isAdmin
     */
    public function __construct(\google\appengine\api\users\User $user, $isAdmin = false)
    {
        $this->user = $user;
        $this->isAdmin = $isAdmin;
    }

    /**
     * {@inheritdocs}.
     */
    public function getRoles()
    {
        return $this->isAdmin ? array('ROLE_SUPER_ADMIN', 'ROLE_USER') : array('ROLE_USER');
    }

    /**
     * {@inheritdocs}.
     */
    public function getPassword()
    {
        return '';
    }

    /**
     * {@inheritdocs}.
     */
    public function getSalt()
    {
    }

    /**
     * {@inheritdocs}.
     */
    public function getUsername()
    {
        return $this->user->getNickname();
    }

    /**
     * {@inheritdocs}.
     */
    public function eraseCredentials()
    {
    }
}
