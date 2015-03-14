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

class User {
    /** @var \LO\Application  */
    private $app;

    public function __construct(Application $app){
        $this->app = $app;
    }

    public function findByToken($token){
        return $this->app->getEntityManager()->createQueryBuilder()
                    ->select('u')
                    ->from(EntityUser::class, 'u')
                    ->join(Token::class, 't', Expr\Join::WITH)
                    ->where('t.hash = :token')
                    ->setParameter('token', $token)
                    ->getQuery()
                    ->execute();
        ;
    }

    public function findByEmailPassword($email, $password){
        return false;
    }
} 