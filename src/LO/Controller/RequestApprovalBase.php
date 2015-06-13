<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 5/11/15
 * Time: 11:56 AM
 */

namespace LO\Controller;


use LO\Application;
use LO\Exception\Http;
use LO\Form\QueueType;
use LO\Model\Entity\Queue;
use LO\Model\Manager\QueueManager;
use LO\Traits\GetFormErrors;

class RequestApprovalBase extends RequestBaseController{
    use GetFormErrors;

    /**
     * @param Application $app
     * @param $id
     * @return Queue
     * @throws \LO\Exception\Http
     */
    protected function getQueueById(Application $app, $id){
        /** @var Queue $queue */
        $queue = $app->getFactory()->queueManager($app)->getByIdWithUser($id);

        if(!$queue){
            throw new Http(sprintf("Property approval \'%s\' not found.", $id), Response::HTTP_BAD_REQUEST);
        }

        return $queue;
    }

    /**
     * @param Application $app
     * @param Queue $queue
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    protected function getResponse(Application $app, Queue $queue){
        $queueForm = $app->getFormFactory()->create(new QueueType($app->getS3()), $queue);

        return $app->json([
            'property' => $this->getFormFieldsAsArray($queueForm),
            'address'  => $queue->getAdditionalInfo(),
            'user'     => $queue->getUser()->getPublicInfo(),
        ]);
    }
} 