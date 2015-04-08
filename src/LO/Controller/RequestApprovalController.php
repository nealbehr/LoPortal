<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 4/8/15
 * Time: 5:40 PM
 */

namespace LO\Controller;

use LO\Application;
use LO\Exception\Http;
use LO\Form\QueueForm;
use LO\Model\Entity\RequestApproval;
use LO\Model\Entity\Queue;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use LO\Traits\GetFormErrors;

class RequestApprovalController extends RequestBaseController{
    use GetFormErrors;

    public function AddAction(Application $app, Request $request){
        try{
            $app->getEntityManager()->beginTransaction();
            $data = [];
            $id = $this->sendRequestTo1Rex($app, $request->get('address'), (string)$app->user());

            $queue = (new Queue())
                ->set1RexId($id)
                ->setType(Queue::TYPE_FLYER)
                ->setUserId($app->user()->getId())
            ;

            $queueForm = $app->getFormFactory()->create(new QueueForm(), $queue);
            $queueForm->handleRequest($request);

            if(!$queueForm->isValid()){
                $data = array_merge($data, ['property' => $this->getFormErrors($queueForm)]);

                throw new Http('Property info is not valid', Response::HTTP_BAD_REQUEST);
            }

            $app->getEntityManager()->persist($queue);
            $app->getEntityManager()->flush();

            $requestApproval = (new RequestApproval())->setQueueId($queue->getId());

            $app->getEntityManager()->persist($requestApproval);
            $app->getEntityManager()->flush();

            $app->getEntityManager()->commit();
        }catch (\Exception $e){
            $app->getEntityManager()->rollback();
            $app->getMonolog()->addError($e);
            $data['message'] = $e instanceof Http? $e->getMessage(): 'We have some problems. Please try later.';
            return $app->json($data, $e instanceof Http? $e->getStatusCode(): Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $app->json("success");
    }
}