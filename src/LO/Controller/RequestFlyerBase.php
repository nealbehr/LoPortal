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
use LO\Model\Entity\QueueRealtor;
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
        QueueRealtor $realtor,
        Queue $queue,
        array $formOptions = []
    ) {
        $formOptionsCopy = $formOptions;
        if ($queue->getOmitRealtorInfo() === '1') {
            $formOptionsCopy['validation_groups'] = ['Default', 'draft'];
        }

        // Get realtor
        if (!empty($request->get('realtor_id'))) {
            $realtor = $this->getRealtorById($app, $request->get('realtor_id'));
        }
        // Create realtor
        else {
            $form = $app->getFormFactory()->create(new RealtorForm($app->getS3()), new QueueRealtor, $formOptionsCopy);
            $form->handleRequest($request);
            if (!$form->isValid()) {
                $this->getMessage()->replace('realtor', $this->getFormErrors($form));

                throw new Http('Realtor info is not valid', Response::HTTP_BAD_REQUEST);
            }
            $realtor->setUserId($app->getSecurityTokenStorage()->getToken()->getUser()->getId());
            $app->getEntityManager()->persist($realtor);
            $app->getEntityManager()->flush();
        }

        // Set realtor
        if ($realtor instanceof QueueRealtor) {
            $queue->setRealtor($realtor);
        }

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
     * @return null|QueueRealtor
     */
    protected function getRealtorById(Application $app, $id)
    {
        return $app->getEntityManager()
            ->getRepository(QueueRealtor::class)
            ->findOneBy(['id' => $id, 'user_id' => $app->getSecurityTokenStorage()->getToken()->getUser()->getId()]);
    }
}