<?php namespace LO\Controller\Admin;

use LO\Application;
use Symfony\Component\HttpFoundation\Request;
use LO\Model\Entity\SalesDirector;

class SalesDirectorController extends Base
{
    const LIMIT = 20;

    const DEFAULT_SORT_FIELD_NAME = 'id';
    const DEFAULT_SORT_DIRECTION  = 'asc';

    public function getListAction(Application $app, Request $request)
    {
        $pagination = $app->getPaginator()->paginate(
            $this->getSalesDirectorList($request, $app),
            (int)$request->get(self::KEY_PAGE, 1),
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

    public function addAction()
    {

    }

    public function updateAction()
    {

    }

    public function deleteAction(Application $app, $id)
    {
        try {
            $model = $this->getSalesDirector($app, $id);
            $model->setDeleted('1')->setEmail($model->getEmail().'-'.strtotime('now').'-deleted');
            $app->getEntityManager()->persist($model);
            $app->getEntityManager()->flush();

            return $app->json('success');
        }
        catch (HttpException $e) {
            $app->getMonolog()->addWarning($e);
            $this->errors['message'] = $e->getMessage();

            return $app->json($this->errors, $e->getStatusCode());
        }
    }

    private function getSalesDirectorList(Request $request, Application $app)
    {
        $alias = 'sd';
        $query = $app->getEntityManager()->createQueryBuilder()
            ->select($alias)
            ->from(SalesDirector::class, $alias)
            ->where("$alias.deleted = '0'")
            ->setMaxResults(self::LIMIT)
            ->orderBy(
                $alias.'.'.$this->getOrderKey($request->query->get(self::KEY_SORT)),
                $this->getOrderDirection($request->query->get(self::KEY_DIRECTION), self::DEFAULT_SORT_DIRECTION)
            );

        if ($request->get(self::KEY_SEARCH)) {
            $query->andWhere(
                $app->getEntityManager()->createQueryBuilder()->expr()->orX(
                    $app->getEntityManager()->createQueryBuilder()->expr()->like("LOWER($alias.name)", ':param'),
                    $app->getEntityManager()->createQueryBuilder()->expr()->like("LOWER($alias.email)", ':param'),
                    $app->getEntityManager()->createQueryBuilder()->expr()->like("LOWER($alias.phone)", ':param')
                )
            )->setParameter('param', '%'.strtolower($request->get(self::KEY_SEARCH)).'%');
        }

        return $query->getQuery();
    }

    private function getOrderKey($col)
    {
        return in_array($col, ['id', 'name', 'email', 'created_at'], true) ? $col : self::DEFAULT_SORT_FIELD_NAME;
    }

    private function getSalesDirector(Application $app, $id)
    {
        if (!($data = $app->getEntityManager()->getRepository(SalesDirector::class)->find($id))) {
            throw new BadRequestHttpException('Sales director not found.');
        }

        return $data;
    }
}
