<?php
namespace LO\Controller;

use Doctrine\ORM\Query;
use LO\Application;
use LO\Exception\Http;
use LO\Model\Entity\Token;
use LO\Model\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use LO\Common\Email;
use LO\Model\Entity\RecoveryPassword;
use \Mixpanel;
use BaseCRM\Client as BaseCrmClient;
use LO\Common\BaseCrm\ContactAdapter;

class Authorize
{
    const MAX_EMAILS = 5;

    /**
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function signinAction(Application $app, Request $request)
    {
        if (!($user = $app->getUserManager()->findByEmail($request->get('email')))) {
            return $app->json(['message' => 'Your email address is not recognized'], Response::HTTP_BAD_REQUEST);
        }

        if ($user->inDeleted()) {
            return $app->json(['message' => 'User deleted.'], Response::HTTP_BAD_REQUEST);
        }

        if (
            !$app->getEncoderFactory()->getEncoder($user)->isPasswordValid(
                $user->getPassword(),
                $request->get('password'),
                $user->getSalt()
            )
        ) {
            return $app->json(['message' => 'Entered password is incorrect'], Response::HTTP_BAD_REQUEST);
        }

        if (!$this->signedPMP($app, $user, $request)) {
            return $app->json(['message' => 'Not signed PMP'], Response::HTTP_UNAUTHORIZED);
        }

        $token = $app->getFactory()->token()->setUserId(
            $user->getId())->setExpirationTime($app->getConfigByName('user', 'token.expire')
        );

        $app->getEntityManager()->persist($token);
        $app->getEntityManager()->flush();

        $this->logInLog($app, $user);

        return $app->json($token->getHash());
    }

    public function resetPasswordAction(Application $app, $email){
        try{
            $app->getEntityManager()->beginTransaction();
            $user = $app->getUserManager()->findByEmail($email);

            if(null === $user){
                throw new NotFoundHttpException("There is no account with entered email.");
            }

            $dateExpire = (new \DateTime())->modify(sprintf('+%d day', $app->getConfigByName('user', 'recovery.password.expire.days')));

            $recoveryPassword = (new RecoveryPassword())
                ->setUser($user)
                ->setDateExpire($dateExpire);

            $app->getEntityManager()->persist($recoveryPassword);
            $app->getEntityManager()->flush();

            $app->getFactory()->recoveryPassword($app, $app->getConfigByName('amazon', 'ses', 'source'), $recoveryPassword)
                ->setDestinationList($user->getEmail())
                ->send();

            $app->getEntityManager()->commit();

            return $app->json("Please check your email for the new password.");
        }catch(HttpException $e){
            $app->getEntityManager()->rollback();
            return $app->json(['message' => $e->getMessage()], $e->getStatusCode());
        }
    }

    public function confirmPassword(Application $app, Request $request, $id){
        try{
            $app->getEntityManager()->beginTransaction();
            /** @var RecoveryPassword $recoveryPassword */
            $recoveryPassword = $app->getEntityManager()
                ->createQueryBuilder()
                ->select('r, u')
                ->from(RecoveryPassword::class, 'r')
                ->leftjoin('r.user', 'u')
                ->where('r.id = :id')
                ->andWhere('r.signature = :signature')
                ->setParameters([
                    'id'        => $id,
                    'signature' => $request->request->get('signature'),
                ])
                ->getQuery()
                ->getOneOrNullResult();

            if(null === $recoveryPassword){
                throw new Http("Bad recovery parameters.", Response::HTTP_BAD_REQUEST);
            }

            if($recoveryPassword->getDateExpire() < (new \DateTime())){
                $app->getEntityManager()->remove($recoveryPassword);
                $app->getEntityManager()->flush();

                throw new Http("Data overdue.", Response::HTTP_LOCKED);
            }


            $salt = $recoveryPassword->getUser()->generateSalt();
            $password = $recoveryPassword->getUser()->generatePassword();
            $recoveryPassword->getUser()
                ->setSalt($salt)
                ->setPassword($app->getEncoderDigest()->encodePassword($password, $salt))

            ;

            $app->getEntityManager()->persist($recoveryPassword);
            $app->getEntityManager()->remove($recoveryPassword);
            $app->getEntityManager()->flush();

            $app->getEntityManager()->commit();

            return $app->json(['password' => $password]);
        }catch(Http $e){
            $app->getEntityManager()->rollback();
            $app->getMonolog()->addWarning($e);
            return $app->json(['message' => $e->getMessage()], $e->getStatusCode());
        }
    }

    /**
     * @param Application $app
     * @param User $model
     * @param Request $request
     * @return bool
     */
    private function signedPMP(Application $app, User $model, Request $request)
    {
        if ($model->inFirstTime()) {
            return true;
        }

        if ('1' === $request->get('first_time')) {
            $model->setFirstTime($request->get('first_time'));
            $app->getEntityManager()->persist($model);
            $app->getEntityManager()->flush();

            // Update contact data in Base CRM
            $client  = new BaseCrmClient(['accessToken' => $app->getConfigByName('basecrm', 'accessToken')]);
            $contact = new ContactAdapter($model);
            $client->contacts->update($contact->getId(), $contact->toArray());

            return true;
        }

        return false;
    }

    /**
     * Log for Mixpanel and BaseCRM
     *
     * @param Application $app
     * @param User $model
     * @return bool
     */
    private function logInLog(Application $app, User $model)
    {
        // Mixpanel analytics
        $mp = Mixpanel::getInstance($app->getConfigByName('mixpanel', 'token'));
        $mp->identify($model->getId());
        $mp->track('Log In');

        // Base CRM save event in note
        if (null !== $model->getBaseId()) {
            (new BaseCrmClient(['accessToken' => $app->getConfigByName('basecrm', 'accessToken')]))->notes->create([
                'resource_type' => 'contact',
                'resource_id'   => $model->getBaseId(),
                'content'       => sprintf('%s || Log In || %s', $app->getConfigByName('name'), $model->getEmail())
            ]);
        }

        return true;
    }
}
