<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 3/13/15
 * Time: 2:11 PM
 */

namespace LO\Model\Manager;

use \LO\Application;
use Doctrine\ORM\Query\Expr;
use LO\Model\Entity\Token;
use LO\Model\Entity\User as EntityUser;

class UserManager {
    /** @var \LO\Application  */
    private $app;

    public function __construct(Application $app){
        $this->app = $app;
    }

    public function findByToken($token){
        return $this->app->getEntityManager()
                    ->getRepository(EntityUser::class)
                    ->createQueryBuilder('u')
                    ->select('u')
                    ->join(Token::class, 't', Expr\Join::WITH, 'u.id = t.user_id')
                    ->where('t.hash = :token')
                    ->andWhere('t.expiration_time > :expireTime')
                    ->setParameter('token', $token)
                    ->setParameter('expireTime', new \DateTime())
                    ->setMaxResults(1)
                    ->getQuery()
                    ->getOneOrNullResult()
        ;
    }

    public function findByEmailPassword($email, $password){
        /** @var EntityUser $user */
        $user = $this->app
            ->getEntityManager()
            ->getRepository(EntityUser::class)
            ->findOneBy(['email' => $email])
        ;

        if(!$user || !$this->app['security.encoder_factory']->getEncoder($user)->isPasswordValid($user->getPassword(), $password, $user->getSalt())){
            return false;
        }

        return $user;
    }
} 