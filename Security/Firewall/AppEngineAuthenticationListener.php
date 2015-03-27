<?php

namespace Caxy\AppEngine\Bridge\Security\Firewall;

use Caxy\AppEngine\Bridge\Security\Authentication\AppEngineToken;
use google\appengine\api\users\User;
use google\appengine\api\users\UserService;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;
use Symfony\Component\Security\Http\SecurityEvents;

class AppEngineAuthenticationListener implements ListenerInterface
{
    protected $logger;
    protected $authenticationManager;

    private $tokenStorage;
    private $dispatcher;

    public function __construct(TokenStorageInterface $tokenStorage, AuthenticationManagerInterface $authenticationManager, LoggerInterface $logger = null, EventDispatcherInterface $dispatcher = null)
    {
        $this->tokenStorage = $tokenStorage;
        $this->authenticationManager = $authenticationManager;
        $this->logger = $logger;
        $this->dispatcher = $dispatcher;
    }

    /**
     * Handles pre-authentication.
     *
     * @param GetResponseEvent $event A GetResponseEvent instance
     */
    public function handle(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if (null !== $this->logger) {
            $this->logger->debug(sprintf('Checking secure context token: %s', $this->tokenStorage->getToken()));
        }

        try {
            $user = $this->getCurrentUser($request);
        } catch (BadCredentialsException $exception) {
            $this->clearToken($exception);

            return;
        }

        if (null !== $token = $this->tokenStorage->getToken()) {
            if ($token instanceof AppEngineToken && $token->isAuthenticated() && $token->getUsername() === $user) {
                return;
            }
        }

        if (null !== $this->logger) {
            $this->logger->debug(sprintf('Trying to pre-authenticate user "%s"', $user));
        }

        try {
            $attributes = array_filter(array(
                'auth_domain' => $user->getAuthDomain(),
                'email' => $user->getEmail(),
                'federated_identity' => $user->getFederatedIdentity(),
                'federated_provider' => $user->getFederatedProvider(),
                'user_id' => $user->getUserId(),
            ));
            $token = $this->authenticationManager->authenticate(new AppEngineToken($user->getNickname(), $attributes));

            if (null !== $this->logger) {
                $this->logger->info(sprintf('Authentication success: %s', $token));
            }
            $this->tokenStorage->setToken($token);

            if (null !== $this->dispatcher) {
                $loginEvent = new InteractiveLoginEvent($request, $token);
                $this->dispatcher->dispatch(SecurityEvents::INTERACTIVE_LOGIN, $loginEvent);
            }
        } catch (AuthenticationException $failed) {
            $this->clearToken($failed);
        }
    }

    /**
     * Clears a PreAuthenticatedToken for this provider (if present).
     *
     * @param AuthenticationException $exception
     */
    private function clearToken(AuthenticationException $exception)
    {
        $token = $this->tokenStorage->getToken();
        if ($token instanceof AppEngineToken) {
            $this->tokenStorage->setToken(null);

            if (null !== $this->logger) {
                $this->logger->info(sprintf("Cleared security context due to exception: %s", $exception->getMessage()));
            }
        }
    }

    /**
     * Gets the user and credentials from the Request.
     *
     * @param Request $request A Request instance
     *
     * @return User An array composed of the user and the credentials
     */
    protected function getCurrentUser(Request $request)
    {
        try {
            $user = UserService::getCurrentUser();
        } catch (\InvalidArgumentException $e) {
            throw new BadCredentialsException(sprintf('Invalid Google App Engine User: %s', $e->getMessage()));
        }

        if (!$user) {
            throw new BadCredentialsException(sprintf('No Google App Engine User.'));
        }

        return $user;
    }
}
