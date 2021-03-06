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
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Query\Expr;
use LO\Model\Entity\User;
use LO\Model\Entity\Template;
use LO\Model\Entity\TemplateLender;
use LO\Model\Entity\TemplateAddress;

class DashboardManager extends Base
{
    public function getByUserId($userId, $withoutCanceled = true)
    {
        $q = $this->getApp()->getEntityManager()
            ->createQueryBuilder()
            ->select('q')
            ->from(Queue::class, 'q')
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

    public function getCollateralByUserId($userId, $hydrate = AbstractQuery::HYDRATE_OBJECT)
    {
        return  $this->getApp()
            ->getEntityManager()
            ->createQueryBuilder()
            ->select('q')
            ->from(Queue::class, 'q')
            ->where('q.user_id = :userId')
            ->andWhere('q.state = :state')
            ->andWhere('q.request_type = :request_type')
            ->addOrderBy('q.created_at', 'DESC')
            ->setParameter('userId' , $userId)
            ->setParameter('state', Queue::STATE_APPROVED)
            ->setParameter('request_type', Queue::TYPE_FLYER)
            ->getQuery()
            ->getResult($hydrate)
            ;
    }
}
