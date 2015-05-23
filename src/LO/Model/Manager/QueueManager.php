<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 4/18/15
 * Time: 7:02 PM
 */

namespace LO\Model\Manager;

use LO\Model\Entity\Queue;

class QueueManager extends Base{
    public function getByIdWithRequestFlyeAndrUser($id){
        return $this->getApp()
                    ->getEntityManager()
                    ->createQueryBuilder()
                    ->select('q, f, u, r')
                    ->from(Queue::CLASS_NAME, 'q')
                    ->join('q.flyer', 'f')
                    ->join('f.realtor', 'r')
                    ->join('q.user', 'u')
                    ->where('q.id = :id')
                    ->setParameter('id', $id)
                    ->getQuery()
                    ->getOneOrNullResult();
    }

    public function getById($id){
        return $this->getApp()
                    ->getEntityManager()
                    ->createQueryBuilder()
                    ->select('q')
                    ->from(Queue::CLASS_NAME, 'q')
                    ->where('q.id = :id')
                    ->setParameter('id', $id)
                    ->getQuery()
                    ->getOneOrNullResult();
    }

    public function getByIdWithUser($id){
        return $this->getApp()
                    ->getEntityManager()
                    ->createQueryBuilder()
                    ->select('q, u')
                    ->from(Queue::CLASS_NAME, 'q')
                    ->join('q.user', 'u')
                    ->where('q.id = :id')
                    ->setParameter('id', $id)
                    ->getQuery()
                    ->getOneOrNullResult();
    }
}