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
use LO\Form\UserFormChangePassword;
use LO\Form\UserFormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use LO\Model\Entity\User as UserEntity;
use LO\Model\Entity\Lender;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use BaseCRM\Client as BaseCrmClient;
use LO\Common\BaseCrm\ContactAdapter;
use LO\Traits\GetFormErrors,
    \Mixpanel;

class UserController
{
    use GetFormErrors;

    /** @var array  */
    private $errors = [];

    public function getByIdAction(Application $app, $id){
        try {
            if ("me" == $id || $app->getSecurityTokenStorage()->getToken()->getUser()->getId() == $id) {
//                app.security.token is not null and is_granted("ROLE_PREVIOUS_ADMIN")
                $info = $app->getSecurityTokenStorage()->getToken()->getUser()->getPublicInfo();
                return $app->json(array_merge($info, ['switched' => $app->getAuthorizationChecker()->isGranted("ROLE_PREVIOUS_ADMIN")]));
            }

            if ($app->getSecurityTokenStorage()->getToken()->getUser()->getId() != $id && !$app->getAuthorizationChecker()->isGranted(UserEntity::ROLE_ADMIN)) {
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
     * @param $param
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function searchAction(Application $app, $param)
    {
        try {
            if (!($user = $app->getEntityManager()->getRepository(UserEntity::class)->findOneBy(['email' => $param]))) {
                throw new BadRequestHttpException('User not found.');
            }

            return $app->json($user->getPublicInfo());
        }
        catch(HttpException $e) {
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
    public function updateAction(Application $app, Request $request, $id) {
        $user = null;
        $em   = $app->getEntityManager();
        try{

            if(!($user = $em->getRepository(UserEntity::class)->find($id))){
                throw new BadRequestHttpException("User not found.");
            }

            $userFormType = empty($request->request->get('user')['password']['password'])
                ? new UserFormType($em, $app->getS3())
                : new UserFormChangePassword($app, $app->getS3());

            // Validation user
            $form = $app->getFormFactory()->create(
                $userFormType,
                $user,
                [
                    'validation_groups' => ['Default'],
                    'method'            => 'PUT'
                ]
            );

            $form->submit($request);
            if (!$form->isValid()) {
                $app->getMonolog()->addError($form->getErrors(true));
                $this->errors = $this->getFormErrors($form);
                throw new Http(implode(' ', $this->errors), Response::HTTP_BAD_REQUEST);
            }

            // Set lender
            if (
                !isset($request->get('user')['lender']['id'])
                || !($lender = $em->getRepository(Lender::class)->find($request->get('user')['lender']['id']))
            ) {
                throw new Http('Lender not found.', Response::HTTP_BAD_REQUEST);
            }
            $user->setLender($lender);

            // Save user
            $em->persist($user);
            $em->flush();

            // Mixpanel analytics
            $this->editedProfileLog($app, $user);

            // Update BaseCRM
            $this->updateBaseCrm($app, $user);

            return $app->json('success');

        }catch(HttpException $e){
            if($app->getSecurityTokenStorage()->getToken()->getUser()->getId() == $id){
                $app->getUserProvider()->refreshUser($user);
            }
            $app->getMonolog()->addWarning($e);
            $this->errors['message'] = $e->getMessage();
            return $app->json($this->errors, $e->getStatusCode());
        }finally{
            if($app->getSecurityTokenStorage()->getToken()->getUser()->getId() == $id && $user instanceof UserEntity){
                $em->refresh($user);
            }
        }

        return $app->json('error');
    }

    /**
     * Mixpanel analytics
     *
     * @param Application $app
     * @param User $model
     * @return bool
     */
    private function editedProfileLog(Application $app, UserEntity $model)
    {
        $mp = Mixpanel::getInstance($app->getConfigByName('mixpanel', 'token'));
        $mp->identify($model->getId());
        $mp->track('User profile edited');

        return true;
    }

    /**
     * Update contact in BaseCRM
     *
     * @param Application $app
     * @param User $model
     * @return array
     */
    private function updateBaseCrm(Application $app, UserEntity $model)
    {
        if (null === $model->getBaseId()) {
            return false;
        }

        $client  = new BaseCrmClient(['accessToken' => $app->getConfigByName('basecrm', 'accessToken')]);
        $contact = new ContactAdapter($model);

        return $client->contacts->update($contact->getId(), $contact->toArray());
    }
}