<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 4/20/15
 * Time: 5:30 PM
 */

namespace LO\Controller\Admin;

use LO\Application;
use LO\Exception\Http;
use LO\Form\FirstRexAddress;
use LO\Form\QueueType;
use LO\Model\Entity\Queue;
use LO\Model\Manager\QueueManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use LO\Traits\GetFormErrors;
use LO\Controller\RequestApprovalBase;

class PropertyApproval extends RequestApprovalBase{
    use GetFormErrors;

    public function getAction(Application $app, $id){
        return $this->getResponse($app, $this->getQueueById($app, $id));
    }

    public function updateAction(Application $app, Request $request, $id){
        try{
            $data = [];
            $app->getEntityManager()->beginTransaction();

            /** @var Queue $queue */
            $queue = (new QueueManager($app))->getByIdWithUser($id);

            if(!$queue){
                throw new Http(sprintf('Request \'%s\' not found.', $id), Response::HTTP_BAD_REQUEST);
            }

            $firstRexForm = $app->getFormFactory()->create(new FirstRexAddress(), null, ["method" => "PUT"]);
            $firstRexForm->handleRequest($request);

            if(!$firstRexForm->isValid()){
                $data = array_merge($data, ['address' => $this->getFormErrors($firstRexForm)]);

                throw new Http('Additional info is not valid', Response::HTTP_BAD_REQUEST);
            }

            $id = $this->sendRequestTo1Rex($app, $firstRexForm->getData(), $queue->getUser());

            $queue->set1RexId($id)
                  ->setAdditionalInfo($firstRexForm->getData());

            $queueForm = $app->getFormFactory()->create(new QueueType($app->getS3()), $queue, ["method" => "PUT"]);
//            $queueForm->submit($this->removeExtraFields($request->request->get('property'), $queueForm));
            $queueForm->handleRequest($request);

            if(!$queueForm->isValid()){
                $data = array_merge($data, ['property' => $this->getFormErrors($queueForm)]);

                throw new Http('Property info is not valid', Response::HTTP_BAD_REQUEST);
            }

            $app->getEntityManager()->persist($queue);
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