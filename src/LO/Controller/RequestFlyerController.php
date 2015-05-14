<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 3/25/15
 * Time: 12:36 PM
 */

namespace LO\Controller;


use LO\Application;
use LO\Common\Email\Request\RequestChangeStatus;
use LO\Common\Email\Request\RequestFlyerApproval;
use LO\Common\Email\Request\RequestFlyerSubmission;
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

class RequestFlyerController extends RequestBaseController{
    use GetFormErrors;

    public function getAction(Application $app, $id){
        $queue = $this->getQueueById($app, $id);

        $queueForm = $app->getFormFactory()->create(new QueueForm(), $queue);

        $requestFlyer = $queue->getFlyer();

        $formFlyerForm = $app->getFormFactory()->create(new RequestFlyerForm($app->getS3()), $requestFlyer);

        $realtor = $app->getEntityManager()->getRepository(Realtor::class)->find($requestFlyer->getRealtorId());
        $realtorForm = $app->getFormFactory()->create(new RealtorForm($app->getS3()), $realtor);

        return $app->json([
            'property' => array_merge($this->getFormFieldsAsArray($queueForm), $this->getFormFieldsAsArray($formFlyerForm), ['state' => $queue->getState()]),
            'realtor'  => $this->getFormFieldsAsArray($realtorForm),
            'address'  => $queue->getAdditionalInfo(),
            'user'     => $queue->getUser()->getPublicInfo(),
        ]);
    }

    public function addAction(Application $app, Request $request){
        try {
            $formOptions = ['validation_groups' => ["Default", "main"]];
            $app->getEntityManager()->beginTransaction();

            $firstRexForm = $app->getFormFactory()->create(new FirstRexAddress());
            $firstRexForm->handleRequest($request);

            if(!$firstRexForm->isValid()){
//                $data = array_merge($data, ['address' => $this->getFormErrors($firstRexForm)]);

                throw new Http('Additional info is not valid', Response::HTTP_BAD_REQUEST);
            }

            $id = $this->sendRequestTo1Rex($app, $firstRexForm->getData(), $app->user());

            $realtor      = new Realtor();
            $requestFlyer = new RequestFlyer();
            $queue        = (new Queue())->set1RexId($id)->setAdditionalInfo($firstRexForm->getData());

            $this->saveFlyer(
                $app,
                $request,
                $realtor,
                $queue,
                $requestFlyer,
                $formOptions
            );

            (new RequestChangeStatus($app,  $queue, new RequestFlyerSubmission($realtor, $requestFlyer)))->send();

            $app->getEntityManager()->commit();
        }catch (\Exception $e){
            $app->getEntityManager()->rollback();
            $app->getMonolog()->addError($e);
            $this->getMessage()->replace('message', $e instanceof Http? $e->getMessage(): 'We have some problems. Please try later.');
            return $app->json($this->getMessage()->get(), $e instanceof Http? $e->getStatusCode(): Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $app->json("success");
    }

    public function updateAction(Application $app, Request $request, $id){
        try {
            if (!$app['security']->isGranted(User::ROLE_ADMIN)){
                throw new Http("You do not have privileges.", Response::HTTP_FORBIDDEN);
            }

            $app->getEntityManager()->beginTransaction();
            $formOptions = [
                'validation_groups' => ["Default", "main"],
                'method' => 'PUT'
            ];

            $queue = $this->getQueueById($app, $id);

            $realtor = $this->getRealtorById($app, $queue->getFlyer()->getRealtorId());

            $firstRexForm = $app->getFormFactory()->create(new FirstRexAddress(), null, ['method' => 'PUT']);
            $firstRexForm->handleRequest($request);

            if(!$firstRexForm->isValid()){
//                $data = array_merge($data, ['address' => $this->getFormErrors($firstRexForm)]);

                throw new Http('Additional info is not valid', Response::HTTP_BAD_REQUEST);
            }

            $id = $this->sendRequestTo1Rex($app, $firstRexForm->getData(), $app->user());

            $queue->set1RexId($id)->setAdditionalInfo($firstRexForm->getData());

            $this->saveFlyer(
                $app,
                $request,
                $realtor,
                $queue,
                $queue->getFlyer(),
                $formOptions
            );

            $app->getEntityManager()->commit();
        }catch (\Exception $e){
            $app->getEntityManager()->rollback();
            $app->getMonolog()->addError($e);
            $this->getMessage()->replace('message', $e instanceof Http? $e->getMessage(): 'We have some problems. Please try later.');
            return $app->json($this->getMessage()->get(), $e instanceof Http? $e->getStatusCode(): Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $app->json("success");
    }

    public function draftAddAction(Application $app, Request $request){
        try {
            $app->getEntityManager()->beginTransaction();

            $this->saveFlyer(
                $app,
                $request,
                new Realtor(),
                (new Queue())->setState(Queue::STATE_DRAFT),
                new RequestFlyer(),
                ['validation_groups' => ["Default", "draft"]]
            );

            $app->getEntityManager()->commit();
        }catch (\Exception $e){
            $app->getEntityManager()->rollback();
            $app->getMonolog()->addError($e);
            $this->getMessage()->replace('message', $e instanceof Http? $e->getMessage(): 'We have some problems. Please try later.');
            return $app->json($this->getMessage()->get(), $e instanceof Http? $e->getStatusCode(): Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $app->json("success");
    }

    public function draftUpdateAction(Application $app, Request $request, $id){
        try {
            $app->getEntityManager()->beginTransaction();
            $queue = $this->getQueueById($app, $id);

            if($queue->getState() !== Queue::STATE_DRAFT){
                throw new Http("We can re-save only draft.");
            }

            $realtor = $this->getRealtorById($app, $queue->getFlyer()->getRealtorId());

            $this->saveFlyer(
                $app,
                $request,
                $realtor,
                $queue,
                $queue->getFlyer(),
                ['method' => 'PUT', 'validation_groups' => ["Default", "draft"]]
            );

            $app->getEntityManager()->commit();
        }catch (\Exception $e){
            $app->getEntityManager()->rollback();
            $app->getMonolog()->addError($e);
            $this->getMessage()->replace('message', $e instanceof Http? $e->getMessage(): 'We have some problems. Please try later.');
            return $app->json($this->getMessage()->get(), $e instanceof Http? $e->getStatusCode(): Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $app->json("success");
    }

    public function deleteDraftAction(Application $app, $id){
        try {
            $app->getEntityManager()->beginTransaction();
            $queue = $this->getQueueById($app, $id);

            if($queue->getState() !== Queue::STATE_DRAFT){
                throw new Http("We can remove only draft.");
            }

            $realtor = $this->getRealtorById($app, $queue->getFlyer()->getRealtorId());

            $app->getEntityManager()->remove($queue->getFlyer());
            $app->getEntityManager()->remove($realtor);
            $app->getEntityManager()->remove($queue);
            $app->getEntityManager()->flush();

            $app->getEntityManager()->commit();
        }catch (\Exception $e){
            $app->getEntityManager()->rollback();
            $app->getMonolog()->addError($e);
            $this->getMessage()->replace('message', $e instanceof Http? $e->getMessage(): 'We have some problems. Please try later.');
            return $app->json($this->getMessage()->get(), $e instanceof Http? $e->getStatusCode(): Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $app->json("success");
    }

    public function createFromApprovalRequestAction(Application $app, Request $request, $id){
        try {
            $app->getEntityManager()->beginTransaction();

            /** @var Queue $queue */
            $queue = (new QueueManager($app))->getByIdWithUser($id);

            if(!$queue){
                throw new Http(sprintf("Request flyer \'%s\' not found.", $id), Response::HTTP_BAD_REQUEST);
            }

            if ($app->user()->getId() != $queue->getUser()->getId() && !$app['security']->isGranted(User::ROLE_ADMIN)){
                throw new Http("You do not have privileges.", Response::HTTP_FORBIDDEN);
            }

            $queue->setType(Queue::TYPE_FLYER);

            $realtor = new Realtor();
            $flyer = new RequestFlyer();

            $this->saveFlyer(
                $app,
                $request,
                $realtor,
                $queue,
                $flyer,
                ['method' => 'PUT', 'validation_groups' => ["Default", "fromPropertyApproval"]]
            );

            (new RequestChangeStatus($app, $queue, new RequestFlyerApproval($realtor, $flyer, $request->getSchemeAndHttpHost())))
                ->send();

            $app->getEntityManager()->commit();
        }catch (\Exception $e){
            $app->getEntityManager()->rollback();
            $app->getMonolog()->addError($e);
            $this->getMessage()->replace('message', $e instanceof Http? $e->getMessage(): 'We have some problems. Please try later.');
            return $app->json($this->getMessage()->get(), $e instanceof Http? $e->getStatusCode(): Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $app->json("success");
    }

    /**
     * @param Application $app
     * @param $id
     * @return Queue
     * @throws \LO\Exception\Http
     */
    private function getQueueById(Application $app, $id){
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
    private function getRealtorById(Application $app, $id){
        return $app->getEntityManager()
            ->getRepository(Realtor::class)
            ->find($id);
    }

    private function saveFlyer(Application $app, Request $request, Realtor $realtor, Queue $queue, RequestFlyer $requestFlyer, array $formOptions = []){
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
}