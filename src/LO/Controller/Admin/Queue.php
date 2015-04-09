<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 4/7/15
 * Time: 3:30 PM
 */

namespace LO\Controller\Admin;


use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Query;
use LO\Application;
use LO\Model\Entity\Queue as EntityQueue;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\Query\Expr;

class Queue extends Base{
    const QUEUE_LIMIT = 20;

    const DEFAULT_SORT_FIELD_NAME = 'created_at';
    const DEFAULT_SORT_DIRECTION  = 'desc';

    public function getAction(Application $app, Request $request){
        /** @var \Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination $pagination */
        $pagination = $app->getPaginator()->paginate(
            $this->getQueueList($request, $app),
            (int) $request->get(self::KEY_PAGE, 1),
            self::QUEUE_LIMIT,
            [
                'pageParameterName'          => self::KEY_PAGE,
                'sortFieldParameterName'     => self::KEY_SORT,
                'filterValueParameterName'   => self::KEY_SEARCH,
                'sortDirectionParameterName' => self::KEY_DIRECTION,
                'defaultSortFieldName'       => self::DEFAULT_SORT_FIELD_NAME,
                'defaultSortDirection'       => self::DEFAULT_SORT_DIRECTION,
            ]
        );

        $items = [];
        $ids = [];
        /** @var EntityQueue $item */
        foreach($pagination->getItems() as $item){
            $items[] = $item->toArray();
            $ids[] = $item->getId();
        }

        $duplicates = $this->getDuplicates($app, $ids);
        foreach($items as &$item){
            $item['duplicates'] = isset($duplicates[$item['id']])? $duplicates[$item['id']]: [];
        }

        return $app->json([
            'pagination'    => $pagination->getPaginationData(),
            'keySearch'     => self::KEY_SEARCH,
            'keySort'       => self::KEY_SORT,
            'keyDirection'  => self::KEY_DIRECTION,
            'queue'         => $items,
            'defDirection'  => self::DEFAULT_SORT_DIRECTION,
            'defField'      => self::DEFAULT_SORT_FIELD_NAME,
        ]);
    }

    private function getDuplicates(Application $app, array $ids){
        if(count($ids) == 0){
            return [];
        }
        $app->getEntityManager()->getConfiguration()->addCustomHydrationMode('Duplicates', '\LO\Bridge\Doctrine\Hydrator\Duplicates');
        return $app->getEntityManager()->getRepository(EntityQueue::class)->createQueryBuilder('q1')
            ->select('q1.id, q2.id, q2.created_at')
            ->leftJoin(EntityQueue::class, 'q2', Expr\Join::WITH, "q1.mls_number = q2.mls_number")
            ->where('q1.id <> q2.id')
            ->getQuery()
            ->getResult('Duplicates');
        ;
    }

    private function getQueueList(Request $request, Application $app){
        $q = $app->getEntityManager()->createQueryBuilder()
            ->select('q1')
            ->from(EntityQueue::class, 'q1')
            ->setMaxResults(static::QUEUE_LIMIT)
            ->orderBy($this->getOrderKey($request->query->get(self::KEY_SORT)), $this->getOrderDirection($request->query->get(self::KEY_DIRECTION), self::DEFAULT_SORT_DIRECTION))
        ;

        if($request->get(self::KEY_SEARCH)){
            $q->andWhere(
                $app->getEntityManager()->createQueryBuilder()->expr()->orX(
                    $app->getEntityManager()->createQueryBuilder()->expr()->like("LOWER(q1.address)", ':param'),
                    $app->getEntityManager()->createQueryBuilder()->expr()->like("LOWER(q1.mls_number)", ':param')
                )
            )
                ->setParameter('param', '%'.strtolower($request->get(self::KEY_SEARCH)).'%')
            ;
        }

        return $q->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true);
    }

    private function getOrderKey($id){
        $allowFields = ['id', 'user_id', 'address', 'mls_number', 'created_at', 'request_type', 'state'];

        return 'q1.'.(in_array($id, $allowFields)? $id: self::DEFAULT_SORT_FIELD_NAME);
    }
} 