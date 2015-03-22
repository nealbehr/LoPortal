<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 3/16/15
 * Time: 1:35 PM
 */

/**
 * This class is API key authenticator for the Symfony Security component,
 * implementing its SimplePreAuthenticatorInterface
 *
 * @see http://symfony.com/doc/current/cookbook/security/api_key_authentication.html
 */


namespace LO\Security;

use Symfony\Component\Security\Core\Authentication\SimplePreAuthenticatorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response;
use Psr\Log\LoggerInterface;

use LO\Provider\ApiKeyUserProvider;


class ApiKeyAuthenticator implements SimplePreAuthenticatorInterface, AuthenticationFailureHandlerInterface{
    const PARAM_NAME = 'x-session-token';

    protected $userProvider;

    public function __construct(ApiKeyUserProvider $userProvider, LoggerInterface $logger)
    {
        $this->userProvider = $userProvider;
    }

    public function createToken(Request $request, $providerKey)
    {
        if (!$request->headers->has(self::PARAM_NAME)) {
            throw new BadCredentialsException('No API key found');
        }

        return new PreAuthenticatedToken(
            'anon.',
            $request->headers->get(self::PARAM_NAME),
            $providerKey
        );
    }

    public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey)
    {
        $apiKey = $token->getCredentials();
        $user = $this->userProvider->getUsernameForApiKey($apiKey);

        if (!$user) {
            throw new AuthenticationException(
                sprintf('API Key "%s" does not exist', $apiKey)
            );
        }

//        $user = $this->userProvider->loadUserByUsername($username);

        return new PreAuthenticatedToken(
            $user,
            $apiKey,
            $providerKey,
            $user->getRoles()
        );
    }

    public function supportsToken(TokenInterface $token, $providerKey)
    {
        return $token instanceof PreAuthenticatedToken && $token->getProviderKey() === $providerKey;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $Exception)
    {
        return new Response("Authentication Failed.", Response::HTTP_FORBIDDEN);
    }
}