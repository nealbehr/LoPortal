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
use LO\Form\QueueForm;
use LO\Form\RealtorForm;
use LO\Form\RequestFlyerForm;
use LO\Model\Entity\Queue;
use LO\Model\Entity\Realtor;
use LO\Model\Entity\RequestFlyer;
use LO\Model\Entity\User;
use LO\Model\Manager\QueueManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use LO\Traits\GetFormErrors;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class RequestFlyerBase extends RequestBaseController{
    use GetFormErrors;

    protected function saveFlyer(Application $app, Request $request, Realtor $realtor, Queue $queue, RequestFlyer $requestFlyer, array $formOptions = []){
        $form = $app->getFormFactory()->create(new RealtorForm($app->getS3()), $realtor, $formOptions);
        $form->handleRequest($request);
        if(!$form->isValid()){
            $this->getMessage()->replace('realtor', $this->getFormErrors($form));

            throw new Http('Realtor info is not valid', Response::HTTP_BAD_REQUEST);
        }

        $app->getEntityManager()->persist($realtor);
        $app->getEntityManager()->flush();

        $queue
            ->setType(Queue::TYPE_FLYER)
            ->setUser($app->user())
        ;

        $queueForm = $app->getFormFactory()->create(new QueueForm(), $queue, $formOptions);
        $queueForm->submit($this->removeExtraFields($request->request->get('property'), $queueForm));

        if(!$queueForm->isValid()){
            $this->getMessage()->replace('property', $this->getFormErrors($queueForm));

            throw new Http('Property info is not valid', Response::HTTP_BAD_REQUEST);
        }

        $app->getEntityManager()->persist($queue);
        $app->getEntityManager()->flush();

        $requestFlyer
            ->setRealtorId($realtor->getId())
            ->setQueue($queue)
        ;

        $formRequestFlyer = $app->getFormFactory()->create(new RequestFlyerForm($app->getS3()), $requestFlyer, $formOptions);
        $formRequestFlyer->submit($this->removeExtraFields($request->request->get('property'), $formRequestFlyer));

        if(!$formRequestFlyer->isValid()){
            $this->getMessage()->replace('property', $this->getFormErrors($form));

            throw new Http('Property info is not valid', Response::HTTP_BAD_REQUEST);
        }

        $app->getEntityManager()->persist($requestFlyer);
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
            throw new Http(sprintf("Request flyer \'%s\' not found.", $id), Response::HTTP_BAD_REQUEST);
        }

        if ($app->user()->getId() != $queue->getUser()->getId() && !$app['security']->isGranted(User::ROLE_ADMIN)) {
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

    protected function update(Application $app, Request $request, Queue $queue){
        $formOptions = [
            'validation_groups' => ["Default", "main"],
            'method' => 'PUT'
        ];

        $realtor = $this->getRealtorById($app, $queue->getFlyer()->getRealtorId());

        $firstRexForm = $app->getFormFactory()->create(new FirstRexAddress(), null, ['method' => 'PUT']);
        $firstRexForm->handleRequest($request);

        if(!$firstRexForm->isValid()){
//                $data = array_merge($data, ['address' => $this->getFormErrors($firstRexForm)]);

            throw new BadRequestHttpException('Additional info is not valid');
        }

        $id = $this->sendRequestTo1Rex($app, $firstRexForm->getData(), $app->user());

        $queue->set1RexId($id)
            ->setAdditionalInfo($firstRexForm->getData())
        ;

        $this->saveFlyer(
            $app,
            $request,
            $realtor,
            $queue,
            $queue->getFlyer(),
            $formOptions
        );
    }
}