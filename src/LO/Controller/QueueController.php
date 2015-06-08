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
use LO\Model\Entity\Queue;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class QueueController {

    public function cancelAction(Application $app, $id){
        /** @var Queue $queue */
        $queue = $app->getEntityManager()->getRepository(Queue::class)->findOneBy(['id' => $id, 'user_id' => $app->user()->getId()]);

        if(!$queue){
            throw new Http(sprintf('Queue \'%s\' not found', $id), Response::HTTP_BAD_REQUEST);
        }
        $queue->setState(Queue::STATE_DECLINED);

        $app->getEntityManager()->persist($queue);
        $app->getEntityManager()->flush();

        return $app->json('success');
    }

    /**
     * Used to delete queue by id ( should work only for declined queues)
     *
     * @param Application $app
     * @param $id
     * @return JsonResponse
     * @throws \LO\Exception\Http
     */
    public function deleteAction(Application $app, $id) {

        $em = $app->getEntityManager();
        $currentUserID = $app->user()->getId();
        $queue = $em->getRepository(Queue::class)->findOneBy(
            ['id' => $id, 'user_id' => $currentUserID, 'state' => Queue::STATE_DECLINED]
        );
        if(!$queue){
            throw new Http(sprintf('Queue \'%s\' not found', $id), Response::HTTP_BAD_REQUEST);
        }

        $queue = $em->getRepository(Queue::class)->findOneBy(['id' => $id]);
        try {
            $em->beginTransaction();
            if($queue) {
                /* @var Queue $queue */
                $realtor = $queue->getRealtor();
                if($realtor) {
                    $em->remove($realtor);
                }
            }
            $em->remove($queue);
            $em->flush();
            $em->commit();
        } catch (\Exception $ex) {
            $em->rollback();
            $app->getMonolog()->addError($ex);
            return $app->json('error');
        }
        return $app->json('success');
    }
}