<?php
namespace LO\Provider;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use LO\Application,
    LO\Model\Entity\User;

class UserProvider implements UserProviderInterface{
    private $app;

    public function __construct(Application $app){
        $this->app = $app;
    }

    public function loadUserByUsername($email){
        $user = $this->app
                     ->getEntityManager()
                     ->getRepository(User::class)->findBy(['email' => $email])
        ;

        if (!$user) {
            throw new UsernameNotFoundException(sprintf('User token "%s" does not exist.', $email));
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
        return $class === '\LO\Model\Entity\User';
    }
}