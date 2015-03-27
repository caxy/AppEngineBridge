<?php

namespace Caxy\AppEngine\Bridge\Security\Authentication;

use google\appengine\api\users\User;
use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

/**
 * Class AppEngineToken.
 */
class AppEngineToken extends AbstractToken
{
    private $providerKey;

    /**
     * @param User  $user
     * @param array $roles
     */
    public function __construct(User $user, $roles = array())
    {
        parent::__construct($roles);

        $this->setUser($user->getNickname());

        $this->setAttributes(array(
            'auth_domain' => $user->getAuthDomain(),
            'email' => $user->getEmail(),
            'federated_identity' => $user->getFederatedIdentity(),
            'federated_provider' => $user->getFederatedProvider(),
            'user_id' => $user->getUserId(),
        ));

        parent::setAuthenticated(true);
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

    /**
     * {@inheritdoc}
     */
    public function setAuthenticated($authenticated)
    {
        if ($authenticated) {
            throw new \LogicException('You cannot set this token to authenticated after creation.');
        }

        parent::setAuthenticated(false);
    }
}
