<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 3/27/15
 * Time: 4:26 PM
 */

namespace LO\Controller;


use LO\Application;
use LO\Exception\Http;
use LO\Model\Entity\Queue as QueueEntity;
use Symfony\Component\HttpFoundation\Response;

class Queue {
    public function cancelAction(Application $app, $id){
        /** @var QueueEntity $queue */
        $queue = $app->getEntityManager()->getRepository(QueueEntity::class)->findOneBy(['id' => $id, 'user_id' => $app->user()->getId()]);

        if(!$queue){
            throw new Http(sprintf('Queue \'%s\' not found', $id), Response::HTTP_BAD_REQUEST);
        }

        $queue->setState(QueueEntity::STATE_CANCELED);

        $app->getEntityManager()->persist($queue);
        $app->getEntityManager()->flush();

        return $app->json('success');
    }
} 