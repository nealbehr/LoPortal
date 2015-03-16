<?php
namespace LO\Controller;

use Doctrine\ORM\Query;
use LO\Application;
use LO\Model\Entity\Token;
use LO\Model\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Authorize {
    const MAX_EMAILS = 5;
    public function signinAction(Application $app, Request $request){
        $user = $app->getUserManager()->findByEmailPassword($request->get('email'), $request->get('password'));

        if(!$user){
            return $app->json(['message' => 'Entered credentials are not valid'], Response::HTTP_BAD_REQUEST);
        }

        $token = new Token();
//        $token->set

//        1) create token
//        2) save token_get_all
//        3) return hash
//        Response::HTTP_FORBIDDEN

        return $app->json($_POST);
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