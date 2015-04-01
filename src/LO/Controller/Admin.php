<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 3/12/15
 * Time: 4:05 PM
 */

namespace LO\Controller;


use LO\Application;
use LO\Form\UserAdminForm;
use LO\Model\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Admin {
    public function getRolesAction(Application $app){
        return $app->json(User::getAllowedRoles());
    }

    public function addUserAction(Application $app, Request $request){
        try{
            $user = new User();
            $form = $app->getFormFactory()->create(new UserAdminForm(), $user);
            //array_intersect_key($request->request->all(), $form->all()) - remove extra firlds
            $data = array_intersect_key($request->request->get('user'), $form->all());
            $form->submit($data);

            if(!$form->isValid()){
                throw new BadRequestHttpException('User info not valid');
            }

            $user->setSalt($user->generateSalt());
            $user->setPassword($app->encodePassword($user, substr(md5(time()), 0, 10)));

            $app->getEntityManager()->persist($user);
            $app->getEntityManager()->flush();

            return $app->json(['id' => $user->getId()]);
        }catch(HttpException $e){
            $app->getMonolog()->addError($e);
            $errors = [];
            foreach($form as $child){
                if($child->getErrors()->count() > 0){
                    $errors[] = str_replace("ERROR: ", "", (string)$child->getErrors());
                }
            }
            return $app->json(['form_errors' => $errors], $e->getStatusCode());
        }
    }
} 