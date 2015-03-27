<?php

namespace Caxy\AppEngine\Bridge\Security\User;

use google\appengine\api\users\UserService;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Class UserProvider.
 */
class AppEngineUserProvider implements UserProviderInterface
{
    private $userRoles;
    private $adminRoles;

    /**
     * @param $userRoles
     * @param $adminRoles
     */
    public function __construct($userRoles = array(), $adminRoles = array())
    {
        $this->userRoles = $userRoles;
        $this->adminRoles = $adminRoles;
    }

    /**
     * {@inheritdocs}.
     */
    public function loadUserByUsername($username)
    {
        $user = UserService::getCurrentUser();

        if ($user->getNickname() !== $username) {
            $ex = new UsernameNotFoundException(sprintf('Username "%s" does not exist.', $username));
            $ex->setUsername($username);

            throw $ex;
        }

        $roles = $this->userRoles;
        if (UserService::isCurrentUserAdmin()) {
            $roles = array_merge($roles, $this->adminRoles);
        }

        return new AppEngineUser($user, $roles);
    }

    /**
     * {@inheritdocs}.
     */
    public function refreshUser(UserInterface $user)
    {
        return $this->loadUserByUsername($user->getUsername());
    }

    /**
     * {@inheritdocs}.
     */
    public function supportsClass($class)
    {
        return $class === 'Caxy\\AppEngine\\Bridge\\Security\\User\\User';
    }
}
