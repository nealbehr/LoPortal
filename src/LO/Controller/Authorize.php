<?php
namespace LO\Controller;

use Doctrine\ORM\Query;
use LO\Application;
use LO\Model\Entity\Token;
use LO\Model\Entity\User;
use LO\Util\TestData;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Authorize {
    const MAX_EMAILS = 5;
    public function signinAction(Application $app, Request $request){
        $user = $app->getUserManager()->findByEmailPassword($request->get('email'), $request->get('password'));

        if(!$user){
            return $app->json(['message' => 'Entered credentials are not valid'], Response::HTTP_BAD_REQUEST);
        }

        $token = (new Token())->setUserId($user->getId())
                              ->setExpirationTime($app->getConfigByName('user', 'token.expire'))
        ;

        $app->getEntityManager()->persist($token);
        $app->getEntityManager()->flush();

        return $app->json($token->getHash());
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

    public function resetPasswordAction(Application $app, $email){
        try{
            $user = $app->getUserManager()->findByEmail($email);

            if($user == null){
                throw new NotFoundHttpException("There is no account with entered email.");
            }

            return $app->json("Please check your email for the new password.");
        }catch(HttpException $e){
            return $app->json(['message' => $e->getMessage()], $e->getStatusCode());
        }
    }
}