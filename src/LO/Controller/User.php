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
use LO\Form\UserForm;
use LO\Model\Manager\UserManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use LO\Model\Entity\User as UserEntity;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;


class User {
    /** @var array  */
    private $errors = [];

    public function getByIdAction(Application $app, $id){
        try {
            if ("me" == $id || $app->user()->getId() == $id) {
//                app.security.token is not null and is_granted("ROLE_PREVIOUS_ADMIN")
                $info =$app->user()->getPublicInfo();
                return $app->json(array_merge($info, ['switched' => $app['security']->isGranted("ROLE_PREVIOUS_ADMIN")]));
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

    /**
     * @param Application $app
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function updateAction(Application $app, Request $request, $id){
        try{
            /** @var UserEntity $user */
            $user = $app->getEntityManager()->getRepository(UserEntity::class)->find($id);

            if(!$user){
                throw new BadRequestHttpException("User not found.");
            }

            $errors = (new UserManager($app))->validateAndSaveUser($request, $user, new UserForm());

            if(count($errors) > 0){
                $this->errors['form_errors'] = $errors;
                throw new BadRequestHttpException('User info not valid.');
            }

            return $app->json('success');
        }catch(HttpException $e){
            $app->getMonolog()->addWarning($e);
            $this->errors['message'] = $e->getMessage();
            return $app->json($this->errors, $e->getStatusCode());
        }
    }
}