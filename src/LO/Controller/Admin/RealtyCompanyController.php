<?php
/**
 * Created by IntelliJ IDEA.
 * User: mac
 * Date: 5/22/15
 * Time: 12:07
 */

namespace LO\Controller\Admin;

use LO\Application;
use LO\Form\RealtyCompanyType;
use LO\Model\Entity\RealtyCompany;
use LO\Model\Entity\User;
use LO\Exception\Http;
use LO\Traits\GetFormErrors;
use Doctrine\ORM\Query;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination;

class RealtyCompanyController extends Base {
    use GetFormErrors;

    const QUEUE_LIMIT = 20;

    const DEFAULT_SORT_FIELD_NAME = 'id';
    const DEFAULT_SORT_DIRECTION = 'desc';

    private $errors = [];

    public function getAllForSelect(Application $app, Request $request) {
        try {
            $em = $app->getEntityManager();
            $allCompanies = $em->getRepository(RealtyCompany::class)->findAll();
            $result = [];
            foreach ($allCompanies as $company) {
                /* @var RealtyCompany $company */
                $result[] = $company->toArray();
            }

            return $app->json($result);
        } catch (\Exception $ex) {
            $app->getMonolog()->addWarning($ex);
        }

    }

    public function getAllAction(Application $app, Request $request)
    {

        $items = [];
        $pagination = null;
        try {
            /** @var SlidingPagination $pagination */
            $pagination = $app->getPaginator()->paginate(
                $this->getCompaniesList($request, $app),
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


            /** @var RealtyCompany $item */
            foreach ($pagination->getItems() as $item) {
                $items[] = $item->toArray();
            }

        } catch (\Exception $ex) {
            $app->getMonolog()->addWarning($ex);
        }

        return $app->json([
            'pagination' => $pagination->getPaginationData(),
            'keySearch' => self::KEY_SEARCH,
            'keySort' => self::KEY_SORT,
            'keyDirection' => self::KEY_DIRECTION,
            'companies' => $items,
            'defDirection' => self::DEFAULT_SORT_DIRECTION,
            'defField' => self::DEFAULT_SORT_FIELD_NAME,
        ]);
    }

    public function getByIdAction(Application $app, $id)
    {
        try {
            /** @var RealtyCompany $company */
            $company = $app->getEntityManager()->getRepository(RealtyCompany::class)->find($id);

            if (!$company) {
                throw new BadRequestHttpException("Company not found.");
            }

            return $app->json($company->toArray());
        } catch (HttpException $e) {
            $app->getMonolog()->addWarning($e);
            return $app->json(['message' => $e->getMessage()], $e->getStatusCode());
        }
    }

    public function addCompanyAction(Application $app, Request $request)
    {
        try {
            $company = new RealtyCompany();
            $requestCompany = $request->request->get('company');
            $companyType = new RealtyCompanyType($app->getS3());
            $formOptions = [
                'validation_groups' => ['Default', 'New'],
            ];
            $form = $app->getFormFactory()->create($companyType, $company, $formOptions);
            $form->submit($requestCompany);

            if (!$form->isValid()) {
                $app->getMonolog()->addError($form->getErrors(true));
                $this->errors = $this->getFormErrors($form);
                throw new BadRequestHttpException("Realty info isn't valid");
            }
            $em = $app->getEntityManager();
            $em->persist($company);
            $em->flush();
            return $app->json(['id' => $company->getId()]);
        } catch (HttpException $e) {
            $app->getMonolog()->addError($e);
            $this->errors['message'] = $e->getMessage();
            return $app->json($this->errors, $e->getStatusCode());
        }
    }

    public function updateCompanyAction(Application $app, Request $request, $id)
    {
        $em = $app->getEntityManager();
        try {
            $company = $app->getEntityManager()->getRepository(RealtyCompany::class)->find($id);
            /* @var RealtyCompany $company */
            $requestCompany = $request->request->get('company');
            $companyType = new RealtyCompanyType($app->getS3());
            $formOptions = [
                'validation_groups' => ['Default', 'New'],
            ];
            $form = $app->getFormFactory()->create($companyType, $company, $formOptions);
            $form->submit($requestCompany);

            if (!$form->isValid()) {
                $this->errors = $this->getFormErrors($form);
                throw new BadRequestHttpException("Company info isn't valid");
            }
            $em->persist($company);
            $em->flush();
        } catch (\Exception $e) {
            var_dump($e);
            $app->getMonolog()->addError($e);
            $this->errors['message'] = $e->getMessage();
            return $app->json($this->errors, $e->getCode());
        }

        return $app->json("success");
    }

    public function deleteAction(Application $app, $id)
    {
        try {
            $em = $app->getEntityManager();
            $company = $em->getRepository(RealtyCompany::class)->find($id);
            /* @var RealtyCompany $company */
            if ($company) {
                $company->setDeleted(true);
                $app->getEntityManager()->persist($company);
                $app->getEntityManager()->flush();
                return $app->json(['status' => 'success']);
            }
            return $app->json('failure');

        } catch (\Exception $e) {
            $app->getMonolog()->addWarning($e);
            $this->errors['message'] = $e->getMessage();
            return $app->json($this->errors, 500);
        }
    }

    private function getCompaniesList(Request $request, Application $app)
    {
        $sort = $this->getOrderKey($request->query->get(self::KEY_SORT));
        $order = $this->getOrderDirection($request->query->get(self::KEY_DIRECTION), self::DEFAULT_SORT_DIRECTION);

        $q = $app->getEntityManager()->createQueryBuilder()
            ->select('rc')
            ->from(RealtyCompany::class, 'rc')
            ->where('rc.deleted = 0')
            ->setMaxResults(static::QUEUE_LIMIT)
            ->orderBy($sort, $order);

        if ($request->get(self::KEY_SEARCH)) {
            $q->andWhere(
                $app->getEntityManager()->createQueryBuilder()->expr()->orX(
                    $app->getEntityManager()->createQueryBuilder()->expr()->like("LOWER(rc.name)", ':param')
                )
            )
                ->setParameter('param', '%' . strtolower($request->get(self::KEY_SEARCH)) . '%');
        }

        return $q->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true);
    }

    private function getOrderKey($id)
    {
        $allowFields = ['id', 'name'];
        return 'rc.' . (in_array($id, $allowFields) ? $id : self::DEFAULT_SORT_FIELD_NAME);
    }
} 