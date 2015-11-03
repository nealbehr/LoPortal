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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class QueueController
{
    public function getAction(Application $app, $id)
    {
        try {
            return $app->json($this->getById($app, $id)->toArray());
        }
        catch (HttpException $e) {
            return $app->json(['message' => $e->getMessage()], $e->getStatusCode());
        }
    }

    public function cancelAction(Application $app, $id)
    {
        $app->getEntityManager()->persist($this->getById($app, $id)->setState(Queue::STATE_DECLINED));
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
        $currentUserID = $app->getSecurityTokenStorage()->getToken()->getUser()->getId();
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

    public function updateStatusAction(Application $app, Request $request)
    {
        $em = $app->getEntityManager();
        try {
            $queue = $em->find(Queue::class, (int)$request->get('id'));

            $queue->setStatusId((int)$request->get('status_id'));
            $queue->setStatusOtherText(filter_var($request->get('status_other_text'), FILTER_SANITIZE_STRING));

            $em->persist($queue);
            $em->flush();

            return $app->json('success');
        }
        catch (HttpException $e) {
            return $app->json(['message' => $e->getMessage()], $e->getStatusCode());
        }
    }

    private function getById(Application $app, $id)
    {
        $qModel = $app->getEntityManager()->getRepository(Queue::class)
            ->findOneBy([
                'id'      => $id,
                'user_id' => $app->getSecurityTokenStorage()->getToken()->getUser()->getId()
            ]);

        if (!$qModel) {
            throw new Http(sprintf('Queue \'%s\' not found', $id), Response::HTTP_BAD_REQUEST);
        }

        return $qModel;
    }
}