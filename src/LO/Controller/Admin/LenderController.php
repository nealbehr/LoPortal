<?php
/**
 * Created by IntelliJ IDEA.
 * User: Dmitry K.
 * Date: 5/14/15
 * Time: 18:36
 */

namespace LO\Controller\Admin;

use LO\Application;
use LO\Form\LenderType;
use LO\Model\Entity\Lender;
use LO\Model\Entity\User;
use LO\Exception\Http;
use LO\Traits\GetFormErrors;
use Doctrine\ORM\Query;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination;

class LenderController extends Base
{

    use GetFormErrors;

    const QUEUE_LIMIT = 20;

    const DEFAULT_SORT_FIELD_NAME = 'created_at';
    const DEFAULT_SORT_DIRECTION = 'desc';

    private $errors = [];

    public function getAllAction(Application $app, Request $request)
    {

        $items = [];
        $pagination = null;
        try {
            /** @var SlidingPagination $pagination */
            $pagination = $app->getPaginator()->paginate(
                $this->getLendersList($request, $app),
                (int)$request->get(self::KEY_PAGE, 1),
                self::QUEUE_LIMIT,
                [
                    'pageParameterName' => self::KEY_PAGE,
                    'sortFieldParameterName' => self::KEY_SORT,
                    'filterValueParameterName' => self::KEY_SEARCH,
                    'sortDirectionParameterName' => self::KEY_DIRECTION,
                    'defaultSortFieldName' => self::DEFAULT_SORT_FIELD_NAME,
                    'defaultSortDirection' => self::DEFAULT_SORT_DIRECTION,
                ]
            );


            /** @var Lender $item */
            foreach ($pagination->getItems() as $item) {
                $items[] = $item->toArray();
            }

        } catch (\Exception $ex) {
            var_dump($ex);
        }

        return $app->json([
            'pagination' => $pagination->getPaginationData(),
            'keySearch' => self::KEY_SEARCH,
            'keySort' => self::KEY_SORT,
            'keyDirection' => self::KEY_DIRECTION,
            'lenders' => $items,
            'defDirection' => self::DEFAULT_SORT_DIRECTION,
            'defField' => self::DEFAULT_SORT_FIELD_NAME,
        ]);
    }

    public function getByIdAction(Application $app, $id)
    {
        try {
            if (!$app->getSecurity()->isGranted(User::ROLE_ADMIN)) {
                throw new Http("You do not have privileges.", Response::HTTP_FORBIDDEN);
            }

            /** @var Lender $lender */
            $lender = $app->getEntityManager()->getRepository(Lender::CLASS_NAME)->find($id);

            if (!$lender) {
                throw new BadRequestHttpException("Lender not found.");
            }

            return $app->json($lender->toArray());
        } catch (HttpException $e) {
            $app->getMonolog()->addWarning($e);
            return $app->json(['message' => $e->getMessage()], $e->getStatusCode());
        }
    }

    public function addLenderAction(Application $app, Request $request)
    {
        try {
            $lender = new Lender();
            $requestLender = $request->request->get('lender');
            $lenderType = new LenderType($app->getS3());
            $formOptions = [
                'validation_groups' => ['Default', 'New'],
            ];
            $form = $app->getFormFactory()->create($lenderType, $lender, $formOptions);
            $form->submit($this->removeExtraFields($requestLender, $form));

            if (!$form->isValid()) {
                $this->errors = $this->getFormErrors($form);
                throw new BadRequestHttpException("Lender info isn't valid");
            }
            $em = $app->getEntityManager();
            $em->persist($lender);
            $em->flush();
            return $app->json(['id' => $lender->getId()]);
        } catch (HttpException $e) {
            $app->getMonolog()->addError($e);
            $this->errors['message'] = $e->getMessage();
            return $app->json($this->errors, $e->getStatusCode());
        }
    }

    public function updateLenderAction(Application $app, Request $request, $id)
    {
        $em = $app->getEntityManager();
        try {
            $lender = $app->getEntityManager()->getRepository(Lender::CLASS_NAME)->find($id);
            $requestLender = $request->request->get('lender');
            $lenderType = new LenderType($app->getS3());
            $formOptions = [
                'validation_groups' => ['Default'],
            ];
            $form = $app->getFormFactory()->create($lenderType, $lender, $formOptions);
            $form->submit($this->removeExtraFields($requestLender, $form));

            if (!$form->isValid()) {
                $this->errors = $this->getFormErrors($form);
                throw new BadRequestHttpException("Lender info isn't valid");
            }
            $em->persist($lender);
            $em->flush();
        } catch (\Exception $e) {
            $app->getMonolog()->addError($e);
            $this->errors['message'] = $e->getMessage();
            return $app->json($this->errors, $e->getCode());
        }

        return $app->json("success");
    }

    public function deleteAction(Application $app, $id)
    {
        try {
            $lender = $app->getEntityManager()->getRepository(Lender::CLASS_NAME)->find($id);
            if ($lender) {
                $app->getEntityManager()->remove($lender);
                $app->getEntityManager()->flush();
                return $app->json('success');
            }
            return $app->json('failure');

        } catch (HttpException $e) {
            $app->getMonolog()->addWarning($e);
            $this->errors['message'] = $e->getMessage();
            return $app->json($this->errors, $e->getStatusCode());
        }
    }

    private function getLendersList(Request $request, Application $app)
    {
        $sort = $this->getOrderKey($request->query->get(self::KEY_SORT));
        $order = $this->getOrderDirection($request->query->get(self::KEY_DIRECTION), self::DEFAULT_SORT_DIRECTION);

        $q = $app->getEntityManager()->createQueryBuilder()
            ->select('q1')
            ->from(Lender::CLASS_NAME, 'q1')
            ->setMaxResults(static::QUEUE_LIMIT)
            ->orderBy($sort, $order);

        if ($request->get(self::KEY_SEARCH)) {
            $q->andWhere(
                $app->getEntityManager()->createQueryBuilder()->expr()->orX(
                    $app->getEntityManager()->createQueryBuilder()->expr()->like("LOWER(q1.name)", ':param'),
                    $app->getEntityManager()->createQueryBuilder()->expr()->like("LOWER(q1.address)", ':param')
                )
            )
                ->setParameter('param', '%' . strtolower($request->get(self::KEY_SEARCH)) . '%');
        }

        return $q->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true);
    }

    private function getOrderKey($id)
    {
        $allowFields = ['id', 'name'];
        return 'q1.' . (in_array($id, $allowFields) ? $id : self::DEFAULT_SORT_FIELD_NAME);
    }
} 