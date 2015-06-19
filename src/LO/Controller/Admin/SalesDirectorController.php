<?php namespace LO\Controller\Admin;

use LO\Application;
use Symfony\Component\HttpFoundation\Request;
use LO\Traits\GetFormErrors;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use LO\Form\SalesDirectorType;
use LO\Model\Entity\SalesDirector;

class SalesDirectorController extends Base
{
    use GetFormErrors;

    const LIMIT                   = 20;
    const DEFAULT_SORT_FIELD_NAME = 'id';
    const DEFAULT_SORT_DIRECTION  = 'asc';

    public function getListAction(Application $app, Request $request)
    {
        $pagination = $app->getPaginator()->paginate(
            $this->getSalesDirectorList($app, $request),
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
            'pagination'     => $pagination->getPaginationData(),
            'keySearch'      => self::KEY_SEARCH,
            'keySort'        => self::KEY_SORT,
            'keyDirection'   => self::KEY_DIRECTION,
            'salesDirectors' => $items,
            'defDirection'   => self::DEFAULT_SORT_DIRECTION,
            'defField'       => self::DEFAULT_SORT_FIELD_NAME,
        ]);
    }

    public function getByIdAction(Application $app, $id)
    {
        try {
            return $app->json($this->getSalesDirectorById($app, $id)->toArray());
        }
        catch (HttpException $e) {
            $app->getMonolog()->addWarning($e);
            return $app->json(['message' => $e->getMessage()], $e->getStatusCode());
        }
    }

    public function addAction(Application $app, Request $request)
    {
        try {
            $app->getEntityManager()->beginTransaction();
            $model = new SalesDirector();

            $this->createForm($app, $request, $model);

            $app->getEntityManager()->persist($model);
            $app->getEntityManager()->flush();
            $app->getEntityManager()->commit();

            return $app->json(['id' => $model->getId()]);
        }
        catch (HttpException $e) {
            $app->getEntityManager()->rollback();
            $app->getMonolog()->addError($e);
            $this->errors['message'] = $e->getMessage();

            return $app->json($this->errors, $e->getStatusCode());
        }
    }

    public function updateAction(Application $app, Request $request, $id)
    {
        $model = $this->getSalesDirectorById($app, $id);

        $this->createForm($app, $request, $model);

        $app->getEntityManager()->persist($model);
        $app->getEntityManager()->flush();

        return $app->json('success');
    }

    public function deleteAction(Application $app, $id)
    {
        try {
            $model = $this->getSalesDirectorById($app, $id);
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

    private function createForm(Application $app, Request $request, SalesDirector $model)
    {
        $formOptions = ['validation_groups' => ['Default']];
        $data        = $request->request->get('salesDirector');

        if (isset($data['email']) && $model->getEmail() !== $data['email']) {
            $formOptions['validation_groups'] = array_merge($formOptions['validation_groups'], ['New']);
        }

        $form  = $app->getFormFactory()->create(new SalesDirectorType($app->getS3()), $model, $formOptions);
        $form->submit($request->request->get('salesDirector'));

        if (!$form->isValid()) {
            $app->getMonolog()->addError($form->getErrors(true));
            $this->errors = $this->getFormErrors($form);
            throw new BadRequestHttpException(implode(' ', $this->errors));
        }

        return $form;
    }

    private function getSalesDirectorList(Application $app, Request $request)
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

    private function getSalesDirectorById(Application $app, $id)
    {
        $model = $app->getEntityManager()->getRepository(SalesDirector::class)->find((int)$id);
        if (!$model || $model->getDeleted() !== '0') {
            throw new BadRequestHttpException('Sales director not found.');
        }

        return $model;
    }
}
