<?php
/**
 * Created by IntelliJ IDEA.
 * User: mac
 * Date: 5/14/15
 * Time: 18:36
 */

namespace LO\Controller\Admin;

use Doctrine\ORM\Query;
use LO\Application;
use LO\Model\Entity\Lender;
use Symfony\Component\HttpFoundation\Request;


class LenderController extends Base {

    const QUEUE_LIMIT = 20;

    const DEFAULT_SORT_FIELD_NAME = 'created_at';
    const DEFAULT_SORT_DIRECTION  = 'desc';

    public function getAction(Application $app, Request $request){

        $items = [];
        $pagination = null;
        try {
            /** @var \Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination $pagination */
            $pagination = $app->getPaginator()->paginate(
                $this->getLendersList($request, $app),
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


            /** @var Lender $item */
            foreach($pagination->getItems() as $item){
                $items[] = $item->toArray();
            }

        } catch (\Exception $ex) {
            var_dump($ex);
        }

        return $app->json([
            'pagination'    => $pagination->getPaginationData(),
            'keySearch'     => self::KEY_SEARCH,
            'keySort'       => self::KEY_SORT,
            'keyDirection'  => self::KEY_DIRECTION,
            'lenders'       => $items,
            'defDirection'  => self::DEFAULT_SORT_DIRECTION,
            'defField'      => self::DEFAULT_SORT_FIELD_NAME,
        ]);
    }

    private function getLendersList(Request $request, Application $app){
        $expr = $app->getEntityManager()->createQueryBuilder()->expr();
        $q = $app->getEntityManager()->createQueryBuilder()
            ->select('q1')
            ->from(Lender::class, 'q1')
            ->setMaxResults(static::QUEUE_LIMIT)
            ->orderBy($this->getOrderKey($request->query->get(self::KEY_SORT)), $this->getOrderDirection($request->query->get(self::KEY_DIRECTION), self::DEFAULT_SORT_DIRECTION))
        ;

        if($request->get(self::KEY_SEARCH)){
            $q->andWhere(
                $app->getEntityManager()->createQueryBuilder()->expr()->orX(
                    $app->getEntityManager()->createQueryBuilder()->expr()->like("LOWER(q1.name)", ':param'),
                    $app->getEntityManager()->createQueryBuilder()->expr()->like("LOWER(q1.address)", ':param')
                )
            )
                ->setParameter('param', '%'.strtolower($request->get(self::KEY_SEARCH)).'%')
            ;
        }

        return $q->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true);
    }

    private function getOrderKey($id){
        $allowFields = ['id', 'name'];
        return 'q1.'.(in_array($id, $allowFields)? $id: self::DEFAULT_SORT_FIELD_NAME);
    }
} 