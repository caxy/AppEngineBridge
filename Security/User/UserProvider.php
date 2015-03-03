<?php

namespace Caxy\AppEngine\Bridge\Security\User;

use google\appengine\api\users\UserService;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Class UserProvider
 * @package Caxy\AppEngine\Bridge\Security\User
 */
class UserProvider implements UserProviderInterface
{
    /**
     * {@inheritdocs}
     */
    public function loadUserByUsername($username)
    {
        if (UserService::getCurrentUser()->getNickname() !== $username) {
            $ex = new UsernameNotFoundException(sprintf('Username "%s" does not exist.', $username));
            $ex->setUsername($username);

            throw $ex;
        }

        return new User(UserService::getCurrentUser(), UserService::isCurrentUserAdmin());
    }

    /**
     * {@inheritdocs}
     */
    public function refreshUser(UserInterface $user)
    {
        return $this->loadUserByUsername($user->getUsername());
    }

    /**
     * {@inheritdocs}
     */
    public function supportsClass($class)
    {
        return $class === 'Caxy\\AppEngine\\Bridge\\Security\\User\\User';
    }
}
