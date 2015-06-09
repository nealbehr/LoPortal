<?php
/**
 * Created by IntelliJ IDEA.
 * User: Dmitry K.
 * Date: 5/14/15
 * Time: 18:36
 */

namespace LO\Controller\Admin;

use Doctrine\ORM\EntityManager;
use LO\Application;
use LO\Form\LenderType;
use LO\Model\Entity\Lender;
use LO\Model\Entity\LenderDisclosure;
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

    const DEFAULT_SORT_FIELD_NAME = 'id';
    const DEFAULT_SORT_DIRECTION = 'asc';

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
                $items[] = $item->toFullArray();
            }

        } catch (\Exception $ex) {
            $app->getMonolog()->addError($ex);
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

    public function getAllJson(Application $app) {
        $lenders = $app->getEntityManager()->getRepository(Lender::class)->findAll();
        $lendersArray = [];
        foreach($lenders as $lender) {
            /* @var Lender $lender*/
            $lendersArray[] = $lender->toArray();
        }
        return $app->json($lendersArray);
    }

    public function getByIdAction(Application $app, $id)
    {
        try {
            if (!$app->getSecurity()->isGranted(User::ROLE_ADMIN)) {
                throw new Http("You do not have privileges.", Response::HTTP_FORBIDDEN);
            }

            /** @var Lender $lender */
            $lender = $app->getEntityManager()->getRepository(Lender::class)->find($id);
            if (!$lender) {
                throw new BadRequestHttpException("Lender not found.");
            }

            return $app->json($lender->toFullArray());
        } catch (HttpException $e) {
            $app->getMonolog()->addWarning($e);
            return $app->json(['message' => $e->getMessage()], $e->getStatusCode());
        }
    }

    public function addLenderAction(Application $app, Request $request)
    {
        $em = $app->getEntityManager();
        try {
            $em->beginTransaction();
            $lender = new Lender();
            $requestLender = $request->request->get('lender');
            $lenderType = new LenderType($app->getS3());
            $formOptions = [
                'validation_groups' => ['Default', 'New'],
            ];
            $form = $app->getFormFactory()->create($lenderType, $lender, $formOptions);
            $form->handleRequest($request);

            if (!$form->isValid()) {
                $this->errors = $this->getFormErrors($form);
                throw new BadRequestHttpException("Lender info isn't valid");
            }

            $this->saleDisclosures($lender, $em, $requestLender);
            $em->persist($lender);
            $em->flush();
            $em->commit();
            return $app->json(['id' => $lender->getId()]);
        } catch (HttpException $e) {
            $em->rollback();
            $app->getMonolog()->addError($e);
            $this->errors['message'] = $e->getMessage();
            return $app->json($this->errors, $e->getStatusCode());
        }
    }

    public function updateLenderAction(Application $app, Request $request, $id)
    {
        $em = $app->getEntityManager();
        try {
            $em->beginTransaction();
            $lender = $em->getRepository(Lender::class)->find($id);
            /* @var Lender $lender */
            $requestLender = $request->request->get('lender');
            $lenderType = new LenderType($app->getS3());
            $formOptions = [
                'validation_groups' => ['Default'],
                'method' => 'PUT'
            ];
            $form = $app->getFormFactory()->create($lenderType, $lender, $formOptions);
            $form->handleRequest($request);
//            $form->submit($this->removeExtraFields($requestLender, $form));

            if (!$form->isValid()) {
                $this->errors = $this->getFormErrors($form);
                throw new BadRequestHttpException("Lender info isn't valid");
            }

            $this->saleDisclosures($lender, $em, $requestLender);
            $em->persist($lender);
            $em->flush();
            $em->commit();
        } catch (\Exception $e) {
            $em->rollback();
            $app->getMonolog()->addError($e);
            $this->errors['message'] = $e->getMessage();
            return $app->json($this->errors, $e->getCode());
        }

        return $app->json("success");
    }

    private function saveDisclosures() {

    }

    public function deleteAction(Application $app, $id)
    {
        try {
            $em = $app->getEntityManager();
            $lender = $em->getRepository(Lender::class)->find($id);
            if ($lender) {
                $qb = $em->createQueryBuilder();
                $qb ->select('u')
                    ->from(User::class, 'u')
                    ->leftJoin('u.lender', 'l')
                    ->andWhere($qb->expr()->eq('l.id', $id));

                $users = $qb->getQuery()->execute();
                if($users && count($users > 0)) {
                    $message = 'Unable to delete. Lender is used by user with ' . $users[0]->getEmail() . ' email';
                    return $app->json(
                        array(
                            'status' => 'error',
                            'message' => $message
                        )
                    );

                } else {
                    $app->getEntityManager()->remove($lender);
                    $app->getEntityManager()->flush();
                    return $app->json(['status' => 'success']);
                }
            }
            return $app->json('failure');

        } catch (\Exception $e) {
            $app->getMonolog()->addWarning($e);
            $this->errors['message'] = $e->getMessage();
            return $app->json($this->errors, 500);
        }
    }

    private function getLendersList(Request $request, Application $app)
    {
        $sort = $this->getOrderKey($request->query->get(self::KEY_SORT));
        $order = $this->getOrderDirection($request->query->get(self::KEY_DIRECTION), self::DEFAULT_SORT_DIRECTION);

        $q = $app->getEntityManager()->createQueryBuilder()
            ->select('l, ld')
            ->from(Lender::class, 'l')
            ->join('l.disclosures', 'ld')
            ->orderBy($sort, $order);

        if ($request->get(self::KEY_SEARCH)) {
            $q->andWhere(
                $app->getEntityManager()->createQueryBuilder()->expr()->orX(
                    $app->getEntityManager()->createQueryBuilder()->expr()->like("LOWER(l.name)", ':param')
                )
            )
            ->setParameter('param', '%' . strtolower($request->get(self::KEY_SEARCH)) . '%');
        }

        return $q->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true);
    }

    private function getOrderKey($id)
    {
        $allowFields = ['id', 'name'];
        return 'l.' . (in_array($id, $allowFields) ? $id : self::DEFAULT_SORT_FIELD_NAME);
    }

    /**
     * @param $lender
     * @param $em
     * @param $requestLender
     */
    private function saleDisclosures(Lender $lender, EntityManager $em, $requestLender)
    {
        foreach ($lender->getDisclosures() as $disclosure) {
            $em->remove($disclosure);
        }
        $em->flush();
        $disclosures = $requestLender['disclosures'];
        foreach ($disclosures as $disclosure) {
            $lenderDisclosure = new LenderDisclosure();
            $lenderDisclosure->setLender($lender);
            $lenderDisclosure->setState($disclosure['state']);
            $text = $disclosure['disclosure'];
            if($text == null) {
                $text = '';
            }
            $lenderDisclosure->setDisclosure($text);
            $em->persist($lenderDisclosure);
        }
    }
} 