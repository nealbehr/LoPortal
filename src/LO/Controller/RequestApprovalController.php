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
use LO\Form\QueueType;
use LO\Model\Entity\RequestApproval;
use LO\Model\Entity\Queue;
use LO\Model\Manager\QueueManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use LO\Traits\GetFormErrors;
use LO\Common\RequestTo1Rex;
use Mixpanel;

class RequestApprovalController extends RequestApprovalBase
{
    public function AddAction(Application $app, Request $request)
    {
        $data = [];
        $em   = $app->getEntityManager();
        try{
            $em->beginTransaction();

            $user = $app->getSecurityTokenStorage()->getToken()->getUser();

            // Create queue
            $queue = new Queue();
            $queue->setType(Queue::TYPE_PROPERTY_APPROVAL);
            $queue->setUser($user);

            // Validate queue data
            $queueForm = $app->getFormFactory()->create(new QueueType($app->getS3()), $queue);
            $queueForm->submit($this->removeExtraFields($request->request->get('property'), $queueForm));
            $queue->setState(Queue::STATE_REQUESTED);

            if (!$queueForm->isValid()) {
                $errors = $queueForm->getErrorsAsString();
                $app->getMonolog()->addInfo($errors);
                $data = array_merge($data, ['property' => $this->getFormErrors($queueForm)]);

                throw new Http('Property info is not valid', Response::HTTP_BAD_REQUEST);
            }
            $em->persist($queue);
            $em->flush();

            // Get first rex id
            $firstRexForm = $app->getFormFactory()->create(new FirstRexAddress());
            $firstRexForm->handleRequest($request);
            if (!$firstRexForm->isValid()) {
                $data = array_merge($data, ['address' => $this->getFormErrors($firstRexForm)]);
                throw new Http('Additional info is not valid', Response::HTTP_BAD_REQUEST);
            }

//            $rexId = (new RequestTo1Rex($app))
//                ->setAddress($firstRexForm->getData())
//                ->setUser($user)
//                ->setQueue($queue)
//                ->send();
            $rexId = 44;

            // Setting rex id and update this queue
            $queue->setAdditionalInfo($firstRexForm->getData());
            $queue->set1RexId($rexId);
            $em->persist($queue);
            $em->flush();

            (new RequestChangeStatus($app, $queue, new PropertyApprovalSubmission()))->send();

            // Mixpanel analytics
            if ($user !== null) {
                $mp = Mixpanel::getInstance($app->getConfigByName('mixpanel', 'token'));
                $mp->identify($user->getId());
                $mp->track('Property Request');
            }

            $em->commit();
        }
        catch (\Exception $e) {
            $em->rollback();
            $app->getMonolog()->addError($e);
            $data['message'] = $e instanceof Http? $e->getMessage(): 'We have some problems. Please try later.';
            return $app->json($data, $e instanceof Http? $e->getStatusCode(): Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $app->json($queue->toArray());
    }

    public function getAction(Application $app, $id){
        $queue = $this->getQueueById($app, $id);

        if ($app->getSecurityTokenStorage()->getToken()->getUser()->getId() != $queue->getUser()->getId()) {
            throw new Http("You do not have privileges.", Response::HTTP_FORBIDDEN);
        }

        return $this->getResponse($app, $queue);
    }
}