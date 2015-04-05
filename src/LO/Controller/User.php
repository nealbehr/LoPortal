<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 3/23/15
 * Time: 6:03 PM
 */

namespace LO\Controller;


use LO\Application;
use LO\Exception\Http;
use Symfony\Component\HttpFoundation\Response;
use LO\Model\Entity\User as UserEntity;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class User {
    public function getByIdAction(Application $app, $id){
        try {
            if ("me" == $id || $app->user()->getId() == $id) {
//                app.security.token is not null and is_granted("ROLE_PREVIOUS_ADMIN")
                return $app->json($app->user()->getPublicInfo());
            }

            if ($app->user()->getId() != $id && !$app['security']->isGranted(UserEntity::ROLE_ADMIN)) {
                throw new Http("You do not have privileges.", Response::HTTP_FORBIDDEN);
            }

            /** @var UserEntity $user */
            $user = $app->getEntityManager()->getRepository(UserEntity::class)->find($id);

            if (!$user) {
                throw new BadRequestHttpException("User not found.");
            }

            return $app->json($user->getPublicInfo());
        }catch(HttpException $e){
            $app->getMonolog()->addWarning($e);
            return $app->json(['message' => $e->getMessage()], $e->getStatusCode());
        }
    }
} 