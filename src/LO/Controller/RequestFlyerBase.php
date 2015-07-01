<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 5/14/15
 * Time: 4:49 PM
 */

namespace LO\Controller;


use LO\Application;
use LO\Exception\Http;
use LO\Form\FirstRexAddress;
use LO\Form\QueueType;
use LO\Form\RealtorForm;
use LO\Model\Entity\Queue;
use LO\Model\Entity\Realtor;
use LO\Model\Entity\User;
use LO\Model\Manager\QueueManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use LO\Traits\GetFormErrors;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class RequestFlyerBase extends RequestBaseController {

    use GetFormErrors;

    protected function saveFlyer(
        Application $app,
        Request $request,
        Realtor $realtor,
        Queue $queue,
        array $formOptions = []
    ) {
        $formOptionsCopy = $formOptions;
        if ($queue->getOmitRealtorInfo() === '1') {
            $formOptionsCopy['validation_groups'] = ['Default', 'draft'];
        }

        $form = $app->getFormFactory()->create(new RealtorForm($app->getS3()), $realtor, $formOptionsCopy);
        $form->handleRequest($request);
        if(!$form->isValid()){
            $this->getMessage()->replace('realtor', $this->getFormErrors($form));

            throw new Http('Realtor info is not valid', Response::HTTP_BAD_REQUEST);
        }

        $app->getEntityManager()->persist($realtor);
        $app->getEntityManager()->flush();
        $queue->setType(Queue::TYPE_FLYER);

        if($queue->getUser() == null) {
            // do not change queue user when edited by admin
            $queue->setUser($app->getSecurityTokenStorage()->getToken()->getUser());
        }
        $queueForm = $app->getFormFactory()->create(new QueueType($app->getS3()), $queue, $formOptions);
        $queueForm->handleRequest($request);
//        $queueForm->submit($this->removeExtraFields($request->request->get('property'), $queueForm));

        if(!$queueForm->isValid()){
            $this->getMessage()->replace('property', $this->getFormErrors($queueForm));

            throw new Http('Property info is not valid', Response::HTTP_BAD_REQUEST);
        }
        $queue->setRealtor($realtor);
        $app->getEntityManager()->persist($queue);
        $app->getEntityManager()->flush();
    }

    /**
     * @param Application $app
     * @param $id
     * @return Queue
     * @throws \LO\Exception\Http
     */
    protected function getQueueById(Application $app, $id){
        /** @var Queue $queue */
        $queue = (new QueueManager($app))->getByIdWithRequestFlyeAndrUser($id);
        if(!$queue){
            throw new Http(sprintf("Request flyer '%s' not found.", $id), Response::HTTP_BAD_REQUEST);
        }

        if ($app->getSecurityTokenStorage()->getToken()->getUser()->getId() != $queue->getUser()->getId() && !$app->getAuthorizationChecker()->isGranted(User::ROLE_ADMIN)) {
            throw new Http("You do not have privileges.", Response::HTTP_FORBIDDEN);
        }

        return $queue;
    }

    /**
     * @param Application $app
     * @param $id
     * @return null|Realtor
     */
    protected function getRealtorById(Application $app, $id){
        return $app->getEntityManager()
            ->getRepository(Realtor::class)
            ->find($id);
    }
}