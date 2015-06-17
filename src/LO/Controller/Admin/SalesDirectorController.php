<?php namespace LO\Controller\Admin;

use LO\Application;
use Symfony\Component\HttpFoundation\Request;
use LO\Model\Entity\SalesDirector;

class SalesDirectorController extends Base
{
    const LIMIT = 20;

    const DEFAULT_SORT_FIELD_NAME = 'id';
    const DEFAULT_SORT_DIRECTION  = 'asc';

    public function getAllAction(Application $app, Request $request)
    {
        $pagination = $app->getPaginator()->paginate(
            $this->getSalesDirectorList($request, $app),
            (int) $request->get(self::KEY_PAGE, 1),
            self::LIMIT,
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
        foreach ($pagination->getItems() as $item) {
            $items[] = $item->toArray();
        }

        return $app->json([
            'pagination'    => $pagination->getPaginationData(),
            'keySearch'     => self::KEY_SEARCH,
            'keySort'       => self::KEY_SORT,
            'keyDirection'  => self::KEY_DIRECTION,
            'salesDirector' => $items,
            'defDirection'  => self::DEFAULT_SORT_DIRECTION,
            'defField'      => self::DEFAULT_SORT_FIELD_NAME,
        ]);
    }

    private function getSalesDirectorList(Request $request, Application $app)
    {
        $q = $app->getEntityManager()->createQueryBuilder()
            ->select('sd')
            ->from(SalesDirector::class, 'sd')
            ->where("sd.deleted = '0'")
            ->setMaxResults(self::LIMIT);

        if ($request->get(self::KEY_SEARCH)){

        }

        return $q->getQuery();
    }
}
