<?php

namespace Caxy\AppEngine\Bridge\Security\User;

use google\appengine\api\users\User;
use Symfony\Component\Security\Core\User\UserInterface;

class AppEngineUser implements UserInterface
{
    /**
     * @var User
     */
    private $user;

    /**
     * @var array
     */
    private $roles;

    /**
     * @param User  $user
     * @param array $roles
     */
    public function __construct(User $user, array $roles = array())
    {
        $this->user = $user;
        $this->roles = $roles;
    }

    /**
     * {@inheritdocs}.
     */
    public function getRoles()
    {
        return $this->roles;
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
