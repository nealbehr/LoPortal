<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 4/7/15
 * Time: 3:30 PM
 */

namespace LO\Controller\Admin;


use Doctrine\ORM\Query;
use LO\Application;
use LO\Common\Email\Request\PropertyApprovalAccept;
use LO\Common\Email\Request\PropertyApprovalDenial;
use LO\Common\Email\Request\RequestChangeStatus;
use LO\Common\Email\Request\RequestFlyerApproval;
use LO\Common\Email\Request\RequestFlyerDenial;
use LO\Model\Entity\Queue;
use LO\Model\Entity\Realtor;
use LO\Model\Entity\Status;
use LO\Traits\GetEntityErrors;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\Query\Expr;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use LO\Common\Email\Request\RequestInterface;

class QueueController extends Base
{
    use GetEntityErrors;

    const QUEUE_LIMIT = 20;

    const DEFAULT_SORT_FIELD_NAME = 'created_at';
    const DEFAULT_SORT_DIRECTION = 'desc';

    public function getAction(Application $app, Request $request)
    {
        /** @var \Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination $pagination */
        $pagination = $app->getPaginator()->paginate(
            $this->getQueueList($request, $app),
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

        $items = [];
        $ids = [];
        /** @var Queue $item */
        foreach ($pagination->getItems() as $item) {
            $items[] = $item->toArray();
            $ids[] = $item->getId();
        }

        $duplicates = $this->getDuplicates($app, $ids);
        foreach ($items as &$item) {
            $item['duplicates'] = isset($duplicates[$item['id']]) ? $duplicates[$item['id']] : [];
        }

        return $app->json([
            'pagination' => $pagination->getPaginationData(),
            'keySearch' => self::KEY_SEARCH,
            'keySort' => self::KEY_SORT,
            'keyDirection' => self::KEY_DIRECTION,
            'queue' => $items,
            'defDirection' => self::DEFAULT_SORT_DIRECTION,
            'defField' => self::DEFAULT_SORT_FIELD_NAME,
        ]);
    }

    public function declineAction(Application $app, Request $request, $id)
    {
        $em = $app->getEntityManager();
        try {
            $em->beginTransaction();
            /** @var Queue $queue */
            $queue = $em->find(Queue::class, $id);
            if (null === $queue) {
                throw new BadRequestHttpException(sprintf("Request '%s' not found.", $id));
            }

            $queue->setState(Queue::STATE_DECLINED)->setReason($request->request->get('reason'));

            // Set status_other_text or status_id
            $statusId = $request->request->get('statusId');
            if ($statusId === StatusController::DECLINE_OTHER) {
                $statusText  = $request->request->get('other');
                $queue->setStatusOtherText($statusText);
            }
            else {
                $statusModel = $this->statusExist($app, $statusId);
                $statusText  = $statusModel->getText();
                $queue->setStatus($statusModel);
            }

            $em->persist($queue);
            $em->flush();

            $requestInterface = $this->getEmailObject($app, $queue, [
                'note'       => $request->request->get('reason'),
                'statusText' => $statusText
            ]);
            $requestChangeStatus = new RequestChangeStatus($app, $queue, $requestInterface);
            $requestChangeStatus->send();
            $em->commit();

            return $app->json("success");
        } catch (HttpException $e) {
            $em->rollback();
            $app->getMonolog()->addError($e);
            return $app->json(["message" => $e->getMessage()], $e->getStatusCode());
        }
    }

    public function approveRequestFlyerAction(Application $app, Request $request, $id)
    {
        $realtor = null;
        $em = $app->getEntityManager();
        try {
            $em->beginTransaction();
            $data = [];
            $queue = $em->find(Queue::class, $id);
            if (null === $queue) {
                throw new BadRequestHttpException(sprintf("Request '%s' not found.", $id));
            }

            $statusModel = $this->statusExist($app, $request->request->get('statusId'));
            $queue->setState(Queue::STATE_APPROVED)
                ->setReason($request->request->get('reason'))
                ->setStatus($statusModel);
            $errors = $app->getValidator()->validate($queue);

            if (count($errors) > 0) {
                $data['errors'] = $this->getValidationErrors($errors);

                throw new BadRequestHttpException("Data is not valid.");
            }

            $em->persist($queue);
            $em->flush();

            /** @var Realtor $realtor */
            $realtor = $queue->getRealtor();

            if (!$realtor) {
                throw new HttpException(Response::HTTP_INTERNAL_SERVER_ERROR, sprintf("Realtor \'%s\' not found for flyer .", $queue->getId()));
            }

            (new RequestChangeStatus(
                $app,
                $queue,
                new RequestFlyerApproval(
                    $realtor,
                    $queue,
                    $request->getSchemeAndHttpHost(),
                    [
                        'note'       => $request->request->get('reason'),
                        'statusText' => $statusModel->getText()
                    ]
                )
            ))->send();

            $em->commit();

            return $app->json("success");
        } catch (HttpException $e) {
            $em->rollback();
            $app->getMonolog()->addWarning($e);
            return $app->json(["message" => $e->getMessage()], $e->getStatusCode());
        }
    }

    public function approveRequestApprovalAction(Application $app, Request $request, $id)
    {
        $em = $app->getEntityManager();
        try {
            $em->beginTransaction();
            $data = [];
            $queue = $em->find(Queue::class, $id);

            if (null === $queue) {
                throw new BadRequestHttpException(sprintf("Request '%s' not found.", $id));
            }

            $statusModel = $this->statusExist($app, $request->request->get('statusId'));
            $queue->setState(Queue::STATE_APPROVED)
                ->setReason($request->request->get('reason'))
                ->setStatus($statusModel);
            $errors = $app->getValidator()->validate($queue);

            if (count($errors) > 0) {
                $data['errors'] = $this->getValidationErrors($errors);

                throw new BadRequestHttpException("Data is not valid.");
            }

            $em->persist($queue);
            $em->flush();

            $request = new PropertyApprovalAccept(
                $request->getSchemeAndHttpHost(),
                [
                    'note'       => $request->request->get('reason'),
                    'statusText' => $statusModel->getText()
                ]
            );
            $changeStatusRequest = new RequestChangeStatus($app, $queue, $request);
            $changeStatusRequest->send();

            $em->commit();

            return $app->json("success");
        } catch (HttpException $e) {
            $em->rollback();
            $app->getMonolog()->addWarning($e);
            $data["message"] = $e->getMessage();
            return $app->json($data, $e->getStatusCode());
        }
    }

    /**
     * @param Application $app
     * @param Queue $queue
     * @return RequestInterface
     */
    private function getEmailObject(Application $app, Queue $queue, $data = [])
    {
        $email = $app->getConfigByName('firstrex', 'email', 'teplate', 'denial');
        if ($queue->getType() == Queue::TYPE_PROPERTY_APPROVAL) {
            return new PropertyApprovalDenial($email, $data);
        }

        /** @var Realtor $realtor */
        $realtor = $queue->getRealtor();
        return new RequestFlyerDenial($realtor, $queue, $email, $data);
    }

    private function getDuplicates(Application $app, array $ids)
    {
        if (count($ids) == 0) {
            return [];
        }
        $app->getEntityManager()->getConfiguration()->addCustomHydrationMode('Duplicates', '\LO\Bridge\Doctrine\Hydrator\Duplicates');
        $expr = $app->getEntityManager()->createQueryBuilder()->expr();
        return $app->getEntityManager()->getRepository(Queue::class)->createQueryBuilder('q1')
            ->select('q1.id, q2.id, q2.created_at')
            ->leftJoin(Queue::class, 'q2', Expr\Join::WITH, "q1.address = q2.address")
            ->where('q1.id <> q2.id')
            ->andWhere('q1.created_at > q2.created_at')
            ->andWhere($expr->notIn('q1.state', [Queue::STATE_DRAFT]))
            ->andWhere($expr->notIn('q2.state', [Queue::STATE_DRAFT]))
            ->getQuery()
            ->getResult('Duplicates');
    }

    private function getQueueList(Request $request, Application $app)
    {
        $expr = $app->getEntityManager()->createQueryBuilder()->expr();
        $q = $app->getEntityManager()->createQueryBuilder()
            ->select('q1')
            ->from(Queue::class, 'q1')
            ->where($expr->notIn('q1.state', [Queue::STATE_DRAFT]))
            ->setMaxResults(static::QUEUE_LIMIT)
            ->orderBy($this->getOrderKey($request->query->get(self::KEY_SORT)), $this->getOrderDirection($request->query->get(self::KEY_DIRECTION), self::DEFAULT_SORT_DIRECTION));

        if ($request->get(self::KEY_SEARCH)) {
            $q->andWhere(
                $app->getEntityManager()->createQueryBuilder()->expr()->orX(
                    $app->getEntityManager()->createQueryBuilder()->expr()->like("LOWER(q1.address)", ':param'),
                    $app->getEntityManager()->createQueryBuilder()->expr()->like("LOWER(q1.mls_number)", ':param')
                )
            )
                ->setParameter('param', '%' . strtolower($request->get(self::KEY_SEARCH)) . '%');
        }

        return $q->getQuery()->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true);
    }

    private function getOrderKey($id)
    {
        $allowFields = ['id', 'user_id', 'address', 'mls_number', 'created_at', 'request_type', 'state'];

        return 'q1.' . (in_array($id, $allowFields) ? $id : self::DEFAULT_SORT_FIELD_NAME);
    }

    private function statusExist(Application $app, $id)
    {
        if (!($model = $app->getEntityManager()->getRepository(Status::class)->find((int)$id))) {
                throw new BadRequestHttpException('Status not exist.');
        }

        return $model;
    }
} 