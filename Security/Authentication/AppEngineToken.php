<?php

namespace Caxy\AppEngine\Bridge\Security\Authentication;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

/**
 * Class AppEngineToken.
 */
class AppEngineToken extends AbstractToken
{
    /**
     * @param $user
     * @param array $attributes
     * @param array $roles
     */
    public function __construct($user, array $attributes = array(), array $roles = array())
    {
        parent::__construct($roles);

        $this->setUser($user);
        $this->setAttributes($attributes);

        if ($roles) {
            $this->setAuthenticated(true);
        }
    }

    public function getCredentials()
    {
        return '';
    }

    public function __toString()
    {
        $attributes = '';
        foreach ($this->getAttributes() as $key => $value) {
            $attributes .= ', '.sprintf('%s="%s"', $key, $value);
        }

        return sprintf(substr(parent::__toString(), 0, -1).'%s)', $attributes);
    }
}
