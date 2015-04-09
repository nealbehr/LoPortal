<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 3/27/15
 * Time: 2:15 PM
 */

namespace LO\Model\Manager;


use LO\Model\Entity\Queue;
use LO\Model\Entity\RequestFlyer;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Query\Expr;

class DashboardManager extends Base{
    public function getByUserId($userId, $withoutCanceled = true){
//        $q = $this->getApp()->getEntityManager()
//            ->createQueryBuilder()
//            ->select('q, f.pdf_link, f.photo')
//            ->from(Queue::class, 'q')
//            ->join(RequestFlyer::class, 'f', Expr\Join::WITH, 'q.id = f.queue_id')
//            ->getQuery()
//            ->getResult(AbstractQuery::HYDRATE_ARRAY);//заюзать свой гидратор



        $result = [];
        foreach(Queue::getStates() as $state){
            $result[$state] = [];
        }

         $q = $this->getApp()->getEntityManager()
            ->getRepository(Queue::class)
            ->createQueryBuilder('q')
            ->where('q.user_id = :userId')
            ->orderBy('q.state', 'ASC')
            ->addOrderBy('q.created_at', 'DESC')
            ->setParameters([
                'userId' => $userId,
                'state'  => Queue::STATE_DECLINED,
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