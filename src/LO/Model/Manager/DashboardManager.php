<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 3/27/15
 * Time: 2:15 PM
 */

namespace LO\Model\Manager;


use Doctrine\ORM\Query;
use LO\Model\Entity\Queue;
use LO\Model\Entity\RequestFlyer;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Query\Expr;

class DashboardManager extends Base{
    public function getByUserId($userId, $withoutCanceled = true){
        $q = $this->getApp()->getEntityManager()
            ->createQueryBuilder()
            ->select('q, f')
            ->from(Queue::class, 'q')
            ->leftJoin('q.flyer', 'f')
            ->where('q.user_id = :userId')
            ->orderBy('q.state', 'ASC')
            ->addOrderBy('q.created_at', 'DESC')
            ->setParameter('userId' , $userId)
        ;

        if($withoutCanceled){
            $q->andWhere('q.state <> :state')
              ->setParameter('state', Queue::STATE_DECLINED);
        }

        $queues = $q->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getResult(Query::HYDRATE_ARRAY);


        $result = [];
        foreach(Queue::getStates() as $state){
            $result[$state] = [];
        }

        foreach($queues as $queue){
            $result[$queue['state']][] = $queue;
        }

        return $result;
    }

    public function getCollateralByUserId($userId, $hydrate = AbstractQuery::HYDRATE_OBJECT){
        return  $this->getApp()
                    ->getEntityManager()
                    ->createQueryBuilder()
                    ->select('f, q')
                    ->from(RequestFlyer::class, 'f')
                    ->join('f.queue', 'q')
                    ->where('q.user_id = :userId')
                    ->andWhere('q.state = :state')
                    ->andWhere('f.pdf_link is not null')
                    ->addOrderBy('q.created_at', 'DESC')
                    ->setParameter('userId' , $userId)
                    ->setParameter('state', Queue::STATE_LISTING_FLYER_PENDING)
                    ->getQuery()
                    ->getResult($hydrate)
        ;
    }
}