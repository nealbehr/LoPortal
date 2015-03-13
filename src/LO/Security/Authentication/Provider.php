<?php
namespace LO\Security\Authentication;

use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use LO\Security\Token\UserToken;
use LO\Security\User;

class Provider implements AuthenticationProviderInterface
{
    private $_userProvider;

    private $_timeExpired;

    public function __construct(UserProviderInterface $userProvider, $timeExpired)
    {
        $this->_userProvider = $userProvider;
        $this->_timeExpired  = $timeExpired;
    }

    public function authenticate(TokenInterface $token){
        if($token->getUser() instanceof User){
            if($this->isExpired($token->getUser()->getTimeExpire())){
                return $token;
            }

            throw new AuthenticationException('The authentication failed.');
        }

        /**
         * @var User $user
         */
        $user = $this->getUserProvider()->loadUserByUsername($token->getUsername());

        if ($user && !$this->isExpired($user->getTimeExpire())) {
            $authenticatedToken = new UserToken($user->getRoles());
            $authenticatedToken->setUser($user);

            return $authenticatedToken;
        }

        throw new AuthenticationException('The authentication failed.');
    }

    protected function getUserProvider(){
        return $this->_userProvider;
    }

    protected function getTimeExpired(){
        return $this->_timeExpired;
    }

    public function supports(TokenInterface $token)
    {
        return $token instanceof UserToken;
    }

    protected function isExpired($timeExpired){
        return time() > $timeExpired;

    }
}