<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 3/13/15
 * Time: 2:11 PM
 */

namespace LO\Model\Manager;

use Doctrine\ORM\Query\Expr;
use LO\Model\Entity\Token;
use LO\Model\Entity\User as EntityUser;

class UserManager extends Base{
    public function findByToken($token){
        return $this->getApp()->getEntityManager()
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
        $user = $this->findByEmail($email);

        if(!$user || !$this->getApp()['security.encoder_factory']->getEncoder($user)->isPasswordValid($user->getPassword(), $password, $user->getSalt())){
            return false;
        }

        return $user;
    }

    /**
     * @param $email
     * @return null|EntityUser
     */
    public function findByEmail($email){
        return $this->getApp()
                     ->getEntityManager()
                     ->getRepository(EntityUser::class)
                     ->findOneBy(['email' => $email])
        ;
    }
} 