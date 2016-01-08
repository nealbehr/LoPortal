<?php
/**
 * User: Eugene Lysenko
 * Date: 1/6/16
 * Time: 16:55
 */
namespace LO\Controller\Admin;

use LO\Application;
use Symfony\Component\HttpFoundation\Request;
use LO\Traits\GetFormErrors;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use LO\Form\RealtorType;
use LO\Model\Entity\Realtor;

class RealtorController extends Base
{
    use GetFormErrors;

    const DEFAULT_SORT_FIELD_NAME = 'id';
    const DEFAULT_SORT_DIRECTION  = 'asc';

    private $orderCols            = ['id', 'first_name', 'last_name', 'email', 'created_at'];
    private $autoCompleteCols     = ['first_name', 'last_name'];

    /**
     * @param Application $app
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getListAction(Application $app, Request $request)
    {
        $pagination = $app->getPaginator()->paginate(
            $this->getList($app, $request),
            $request->get(self::KEY_PAGE, 1),
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
            'pagination'   => $pagination->getPaginationData(),
            'keySearch'    => self::KEY_SEARCH,
            'keySort'      => self::KEY_SORT,
            'keyDirection' => self::KEY_DIRECTION,
            'realtors'     => $items,
            'defDirection' => self::DEFAULT_SORT_DIRECTION,
            'defField'     => self::DEFAULT_SORT_FIELD_NAME,
        ]);
    }

    /**
     * @param Application $app
     * @param $id
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getAction(Application $app, $id)
    {
        try {
            return $app->json($this->getById($app, $id)->toArray());
        }
        catch (HttpException $e) {
            $app->getMonolog()->addWarning($e);
            return $app->json(['message' => $e->getMessage()], $e->getStatusCode());
        }
    }

    /**
     * @param Application $app
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function updateAction(Application $app, Request $request, $id)
    {
        try{
            $app->getEntityManager()->beginTransaction();

            $model = $this->getById($app, $id);

            $this->createForm($app, $model, $request);

            $app->getEntityManager()->persist($model);
            $app->getEntityManager()->flush();

            $app->getEntityManager()->commit();

            return $app->json('success');
        }
        catch (HttpException $e) {
            $app->getMonolog()->addWarning($e);
            $this->errors['message'] = $e->getMessage();

            return $app->json($this->errors, $e->getStatusCode());
        }
    }

    /**
     * @param Application $app
     * @param $id
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function deleteAction(Application $app, $id)
    {
        try {
            $model = $this->getById($app, $id);
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

    /**
     * @param Application $app
     * @param Realtor $model
     * @param Request $request
     * @return \Symfony\Component\Form\Form|\Symfony\Component\Form\FormInterface
     */
    private function createForm(Application $app, Realtor $model, Request $request)
    {
        $form = $app->getFormFactory()->create(
            new RealtorType($app->getS3()),
            $model,
            ['validation_groups' => ['Default']]
        );

        if (!$form->submit($request)->isValid()) {
            $app->getMonolog()->addError($form->getErrors(true));
            $this->errors = $this->getFormErrors($form);
            throw new BadRequestHttpException(implode(' ', $this->errors));
        }

        return $form;
    }

    /**
     * @param Application $app
     * @param Request $request
     * @return \Doctrine\ORM\Query
     */
    private function getList(Application $app, Request $request)
    {
        $alias = 'r';
        $query = $app->getEntityManager()
            ->createQueryBuilder()
            ->select($alias)
            ->from(Realtor::class, $alias)
            //->where("$alias.deleted = '0'")
            ->setMaxResults(self::LIMIT)
            ->orderBy(
                $alias.'.'.$this->getOrderKey($request->query->get(self::KEY_SORT)),
                $this->getOrderDirection($request->query->get(self::KEY_DIRECTION), self::DEFAULT_SORT_DIRECTION)
            );

        if ($request->get(self::KEY_SEARCH)) {
            if (in_array($request->get(self::KEY_SEARCH_BY), $this->autoCompleteCols, true)) {
                $where = $app->getEntityManager()->createQueryBuilder()->expr()->orX(
                    $app->getEntityManager()->createQueryBuilder()->expr()->like(
                        "LOWER($alias.".$request->get(self::KEY_SEARCH_BY).")",
                        ':param'
                    )
                );
            }
            else {
                $where = $app->getEntityManager()->createQueryBuilder()->expr()->orX(
                    $app->getEntityManager()->createQueryBuilder()->expr()->like("LOWER($alias.first_name)", ':param'),
                    $app->getEntityManager()->createQueryBuilder()->expr()->like("LOWER($alias.last_name)", ':param'),
                    $app->getEntityManager()->createQueryBuilder()->expr()->like("LOWER($alias.bre_number)", ':param'),
                    $app->getEntityManager()->createQueryBuilder()->expr()->like("LOWER($alias.email)", ':param'),
                    $app->getEntityManager()->createQueryBuilder()->expr()->like("LOWER($alias.phone)", ':param')
                );
            }
            $query->andWhere($where)->setParameter('param', strtolower($request->get(self::KEY_SEARCH)).'%');
        }

        return $query->getQuery();
    }

    /**
     * @param $col
     * @return string
     */
    private function getOrderKey($col)
    {
        return in_array($col, $this->orderCols, true) ? $col : self::DEFAULT_SORT_FIELD_NAME;
    }

    /**
     * @param Application $app
     * @param $id
     * @return null|object
     */
    private function getById(Application $app, $id)
    {
        $model = $app->getEntityManager()->getRepository(Realtor::class)->find($id);
        if (!$model
            //|| $model->getDeleted() !== '0'
        ) {
            throw new BadRequestHttpException('Realtor not found.');
        }

        return $model;
    }
}
