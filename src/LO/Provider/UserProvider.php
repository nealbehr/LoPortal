<?php
namespace LO\Provider;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use LO\Application,
    LO\Security\User;

class UserProvider implements UserProviderInterface{
    private $app;

    public function __construct(Application $app){
        $this->app = $app;
    }

    public function loadUserByUsername($token){
        $user = $this->app->getUserManager()->findByToken($token);

        if (!$user) {
            throw new UsernameNotFoundException(sprintf('User token "%s" does not exist.', $token));
        }

        return $user;
    }

    public function refreshUser(UserInterface $user){
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    public function supportsClass($class){
        return $class === '\LO\Security\User';
    }
}