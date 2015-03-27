<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 3/27/15
 * Time: 2:15 PM
 */

namespace LO\Model\Manager;


use LO\Model\Entity\Queue;

class DashboardManager extends Base{
    public function getByUserId($userId, $withoutCanceled = true){
        $result = [
            Queue::STATE_APPROVED    => [],
            Queue::STATE_IN_PROGRESS => [],
            Queue::STATE_REQUESTED   => [],
        ];

         $q = $this->getApp()->getEntityManager()
            ->getRepository(Queue::class)
            ->createQueryBuilder('q')
            ->where('q.user_id = :userId')
            ->orderBy('q.state', 'ASC')
            ->addOrderBy('q.created_at', 'DESC')
            ->setParameters([
                'userId' => $userId,
                'state'  => Queue::STATE_CANCELED,
            ])
        ;

        if($withoutCanceled){
            $q->andWhere('q.state <> :state');
        }

        $queues = $q->getQuery()->execute();

        /** @var Queue $queue */
        foreach($queues as $queue){
            $result[$queue->getState()][] = $queue->toArray();
        }

        return $result;
    }
} 