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
use Symfony\Component\Form\Form;

class Admin {
    /** @var array  */
    private $errors = [];

    public function getRolesAction(Application $app){
        return $app->json(User::getAllowedRoles());
    }

    public function addUserAction(Application $app, Request $request){
        try{
            $user = new User();

            $this->validateAndSaveUser($app, $request, $user);

            return $app->json(['id' => $user->getId()]);
        }catch(HttpException $e){
            $app->getMonolog()->addError($e);
            $this->errors['message'] = $e->getMessage();
            return $app->json($this->errors, $e->getStatusCode());
        }
    }

    public function updateUserAction(Application $app, Request $request, $id){
        try{
            /** @var User $user */
            $user = $app->getEntityManager()->getRepository(User::class)->find($id);

            if(!$user){
                throw new BadRequestHttpException("User not found.");
            }

            if($user->getId() == $app->user()->getId()){
                throw new BadRequestHttpException("You can't edit self.");
            }

            $this->validateAndSaveUser($app, $request, $user);

            return $app->json('success');
        }catch(HttpException $e){
            $app->getMonolog()->addWarning($e);
            $this->errors['message'] = $e->getMessage();
            return $app->json($this->errors, $e->getStatusCode());
        }
    }

    public function deleteAction(Application $app, Request $request, $id){
        try {
            /** @var User $user */
            $user = $app->getEntityManager()->getRepository(User::class)->find($id);

            if (!$user) {
                throw new BadRequestHttpException("User not found.");
            }

            if ($user->getId() == $app->user()->getId()) {
                throw new BadRequestHttpException("You remove self.");
            }

            $app->getEntityManager()->remove($user);
            $app->getEntityManager()->flush();

            return $app->json('success');
        }catch(HttpException $e){
            $app->getMonolog()->addWarning($e);
            $this->errors['message'] = $e->getMessage();
            return $app->json($this->errors, $e->getStatusCode());
        }
    }

    private function setErrorsForm(Form $form){
        $errors = [];
        foreach($form as $child){
            if($child->getErrors()->count() > 0){
                $errors[] = str_replace("ERROR: ", "", (string)$child->getErrors());
            }
        }

        $this->errors['form_errors'] = $errors;
    }

    /**
     * @param Application $app
     * @param Request $request
     * @param User $user
     * @return void
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     */
    private function validateAndSaveUser(Application $app, Request $request, User $user){
        $requestUser = $request->request->get('user');
        $formOptions = [
            'validation_groups' => ['Default'],
        ];
        if(!$user || (isset($requestUser['email']) && $user->getEmail() != $requestUser['email'])){//remove uniq constrain
            $formOptions['validation_groups'] = array_merge($formOptions['validation_groups'], ["New"]);
        }

        $form = $app->getFormFactory()->create(new UserAdminForm(), $user, $formOptions);

        $form->submit($this->removeExtraFields($requestUser, $form));

        if(!$form->isValid()){
            $this->setErrorsForm($form);
            throw new BadRequestHttpException('User info not valid.');
        }

        $user->setSalt($user->generateSalt());
        $user->setPassword($app->encodePassword($user, substr(md5(time()), 0, 10)));

        $app->getEntityManager()->persist($user);
        $app->getEntityManager()->flush();
    }

    private function removeExtraFields($requestData, $form){
        return array_intersect_key($requestData, $form->all());
    }
} 