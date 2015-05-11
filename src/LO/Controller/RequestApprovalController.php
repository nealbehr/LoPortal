<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 4/8/15
 * Time: 5:40 PM
 */

namespace LO\Controller;

use LO\Application;
use LO\Common\Email\Request\PropertyApprovalSubmission;
use LO\Common\Email\Request\RequestChangeStatus;
use LO\Exception\Http;
use LO\Form\FirstRexAddress;
use LO\Form\QueueForm;
use LO\Model\Entity\RequestApproval;
use LO\Model\Entity\Queue;
use LO\Model\Manager\QueueManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use LO\Traits\GetFormErrors;

class RequestApprovalController extends RequestApprovalBase{
    public function AddAction(Application $app, Request $request){
        try{
            $app->getEntityManager()->beginTransaction();
            $data = [];
            $firstRexForm = $app->getFormFactory()->create(new FirstRexAddress());
            $firstRexForm->handleRequest($request);

            if(!$firstRexForm->isValid()){
                $data = array_merge($data, ['address' => $this->getFormErrors($firstRexForm)]);

                throw new Http('Additional info is not valid', Response::HTTP_BAD_REQUEST);
            }

            $id = $this->sendRequestTo1Rex($app, $firstRexForm->getData(), $app->user());

            $queue = (new Queue())
                ->set1RexId($id)
                ->setType(Queue::TYPE_PROPERTY_APPROVAL)
                ->setUser($app->user())
                ->setAdditionalInfo($firstRexForm->getData())
            ;

            $queueForm = $app->getFormFactory()->create(new QueueForm(), $queue);
            $queueForm->handleRequest($request);

            $queue->setState(Queue::STATE_REQUESTED);

            if(!$queueForm->isValid()){
                $data = array_merge($data, ['property' => $this->getFormErrors($queueForm)]);

                throw new Http('Property info is not valid', Response::HTTP_BAD_REQUEST);
            }

            $app->getEntityManager()->persist($queue);
            $app->getEntityManager()->flush();

            (new RequestChangeStatus($app, $queue, new PropertyApprovalSubmission()))
                ->send();

            $app->getEntityManager()->commit();
        }catch (\Exception $e){
            $app->getEntityManager()->rollback();
            $app->getMonolog()->addError($e);
            $data['message'] = $e instanceof Http? $e->getMessage(): 'We have some problems. Please try later.';
            return $app->json($data, $e instanceof Http? $e->getStatusCode(): Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $app->json("success");
    }

    public function getAction(Application $app, $id){
        $queue = $this->getQueueById($app, $id);

        if ($app->user()->getId() != $queue->getUser()->getId()) {
            throw new Http("You do not have privileges.", Response::HTTP_FORBIDDEN);
        }

        return $this->getResponse($app, $queue);
    }
}