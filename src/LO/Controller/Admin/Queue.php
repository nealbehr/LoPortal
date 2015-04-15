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
use LO\Common\Email\RequestApprove;
use LO\Common\Email\RequestDecline;
use LO\Common\UploadS3\Pdf;
use LO\Model\Entity\Queue as EntityQueue;
use LO\Model\Entity\RequestApproval;
use LO\Traits\GetEntityErrors;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\Query\Expr;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Queue extends Base{
    use GetEntityErrors;
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

    public function declineAction(Application $app, Request $request, $id){
        try {
            $app->getEntityManager()->beginTransaction();
            /** @var EntityQueue $queue */
            $queue = $this->findQueueById($app, $id);

            if(null === $queue){
                throw new BadRequestHttpException(sprintf("Request '%s' not found.", $id));
            }

            $queue->setState(EntityQueue::STATE_DECLINED)
                  ->setReason($request->request->get('reason'));
            $app->getEntityManager()->persist($queue);
            $app->getEntityManager()->flush();


            (new RequestDecline($app, $app->getConfigByName('amazon', 'ses', 'source'), $queue))
                ->setDestinationList($queue->getUser()->getEmail())
                ->send();

            $app->getEntityManager()->commit();

            return $app->json("success");
        }catch(HttpException $e){
            $app->getEntityManager()->rollback();
            $app->getMonolog()->addWarning($e);
            return $app->json(["message" => $e->getMessage()], $e->getStatusCode());
        }
    }

    public function approveRequestFlyerAction(Application $app, Request $request, $id){
        try{
            $app->getEntityManager()->beginTransaction();
            $data = [];
            $queue = $this->findQueueWithRequestFlyerById($app, $id);
            $file = $request->request->get('file');

            if (null === $queue) {
                throw new BadRequestHttpException(sprintf("Request '%s' not found.", $id));
            }

            $queue->setState(empty($file)? EntityQueue::STATE_LISTING_FLYER_PENDING: EntityQueue::STATE_APPROVED)
                  ->setReason($request->request->get('reason'))

            ;

            if(!empty($file)){
                $link = (new Pdf($app->getS3(), $file, '1rex.pdf'))
                    ->downloadFileToS3andGetUrl(time().mt_rand(1, 100000).'.pdf');

                $queue->getFlyer()->setPdfLink($link);
            }

            $errors = $app->getValidator()->validate($queue);

            if(count($errors) > 0){
                $data['errors'] = $this->getValidationErrors($errors);

                throw new BadRequestHttpException("Data is not valid.");
            }

            $app->getEntityManager()->persist($queue);
            $app->getEntityManager()->flush();

            (new RequestApprove($app, $app->getConfigByName('amazon', 'ses', 'source'), $queue))
                ->setDestinationList($queue->getUser()->getEmail())
                ->send();

            $app->getEntityManager()->commit();

            return $app->json("success");
        }catch(HttpException $e){
            $app->getEntityManager()->rollback();
            $app->getMonolog()->addWarning($e);
            return $app->json(["message" => $e->getMessage()], $e->getStatusCode());
        }
    }

    public function approveRequestApprovalAction(Application $app, Request $request, $id){
        try{
            $app->getEntityManager()->beginTransaction();
            $data = [];
            $queue = $this->findQueueById($app, $id);

            if (null === $queue) {
                throw new BadRequestHttpException(sprintf("Request '%s' not found.", $id));
            }

            $queue->setState(EntityQueue::STATE_APPROVED)
                  ->setReason($request->request->get('reason'))

            ;

            $errors = $app->getValidator()->validate($queue);

            if(count($errors) > 0){
                $data['errors'] = $this->getValidationErrors($errors);

                throw new BadRequestHttpException("Data is not valid.");
            }

            $app->getEntityManager()->persist($queue);
            $app->getEntityManager()->flush();

            (new RequestApprove($app, $app->getConfigByName('amazon', 'ses', 'source'), $queue))
                ->setDestinationList($queue->getUser()->getEmail())
                ->send();

            $app->getEntityManager()->commit();

            return $app->json("success");
        }catch(HttpException $e){
            $app->getEntityManager()->rollback();
            $app->getMonolog()->addWarning($e);
            $data["message"] = $e->getMessage();
            return $app->json($data, $e->getStatusCode());
        }
    }

    /**
     * @param Application $app
     * @param $id
     * @return EntityQueue
     */
    private function findQueueWithRequestFlyerById(Application $app, $id){
        return $app->getEntityManager()
            ->createQueryBuilder()
            ->select('q, f, u')
            ->from(EntityQueue::class, 'q')
            ->join('q.flyer', 'f')
            ->join('q.user', 'u')
            ->where('q.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getOneOrNullResult();
    }

    /**
     * @param Application $app
     * @param $id
     * @return EntityQueue
     */
    private function findQueueById(Application $app, $id){
        return $app->getEntityManager()
            ->createQueryBuilder()
            ->select('q, u')
            ->from(EntityQueue::class, 'q')
            ->join('q.user', 'u')
            ->where('q.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->setHint(Query::HINT_FORCE_PARTIAL_LOAD, true)
            ->getOneOrNullResult();
    }

    private function getDuplicates(Application $app, array $ids){
        if(count($ids) == 0){
            return [];
        }
        $app->getEntityManager()->getConfiguration()->addCustomHydrationMode('Duplicates', '\LO\Bridge\Doctrine\Hydrator\Duplicates');
        return $app->getEntityManager()->getRepository(EntityQueue::class)->createQueryBuilder('q1')
            ->select('q1.id, q2.id, q2.created_at')
            ->leftJoin(EntityQueue::class, 'q2', Expr\Join::WITH, "q1.address = q2.address")
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