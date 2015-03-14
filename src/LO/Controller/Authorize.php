<?php
namespace LO\Controller;

use Doctrine\ORM\Query;
use LO\Application;
use LO\Model\Entity\User;
use Symfony\Component\HttpFoundation\Response;

class Authorize {
    const MAX_EMAILS = 5;
    public function loginAction(){
//        Response::HTTP_FORBIDDEN
    }

    public function autocompleteAction(Application $app, $email){
        $app->getEntityManager()->getConfiguration()->addCustomHydrationMode('ListItems', '\LO\Bridge\Doctrine\Hydrator\ListItems');
        $expr = $app->getEntityManager()->createQueryBuilder()->expr();
        $emails = $app->getEntityManager()->createQueryBuilder()
            ->select('u.email')
            ->from(User::class, 'u')
            ->where($expr->like('u.email', ':email'))
            ->setMaxResults(static::MAX_EMAILS)
            ->getQuery()
            ->execute(
                ['email' => '%'.$email.'%'],
                'ListItems'
            )
        ;

        return $app->json($emails);
    }
}