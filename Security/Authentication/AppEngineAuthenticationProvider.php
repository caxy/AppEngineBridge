<?php

namespace Caxy\AppEngine\Bridge\Security\Authentication;

use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class AppEngineAuthenticationProvider implements AuthenticationProviderInterface
{
    private $userProvider;

    public function __construct(UserProviderInterface $userProvider)
    {
        $this->userProvider = $userProvider;
    }

    public function authenticate(TokenInterface $token)
    {
        $user = $this->userProvider->loadUserByUsername($token->getUsername());

        if ($user) {
            $attributes = $token->getAttributes();
            $authenticatedToken = new AppEngineToken($user, $attributes, $user->getRoles());

            return $authenticatedToken;
        }

        throw new AuthenticationException('Google App Engine authentication failed.');
    }

    public function supports(TokenInterface $token)
    {
        return $token instanceof AppEngineToken;
    }
}
