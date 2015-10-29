<?php namespace LO\Controller;

use LO\Application;
use Symfony\Component\HttpFoundation\Request;
use LO\Traits\GetFormErrors;
use Symfony\Component\HttpKernel\Exception\HttpException;
use LO\Model\Entity\Status;
use \Doctrine\ORM\Query;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use LO\Model\Entity\Queue;

class StatusController
{
    use GetFormErrors;

    const KEY_SEARCH_BY           = 'searchBy';
    const KEY_SEARCH              = 'filterValue';
    const DEFAULT_SORT_FIELD_NAME = 'name';
    const DEFAULT_SORT_DIRECTION  = 'asc';
    const DECLINE_OTHER           = 'decline_other';

    private $other = [
        'decline'=> [
            'id'   => 'decline_other',
            'type' => 'decline',
            'name' => 'Other',
            'text' => ''
        ]
    ];

    public function getAllByTypeAction(Application $app, Request $request)
    {
        try {
            $alias = 's';
            $col   = $request->get(self::KEY_SEARCH_BY);
            $col   = in_array($col, ['type'], true) ? $col : 'type';
            $data  = $app->getEntityManager()->createQueryBuilder()
                ->select($alias)
                ->from(Status::class, $alias)
                ->where("$alias.$col = :param")
                ->orderBy("$alias.".self::DEFAULT_SORT_FIELD_NAME, self::DEFAULT_SORT_DIRECTION)
                ->setParameter('param', $request->get(self::KEY_SEARCH))
                ->getQuery()->getResult(Query::HYDRATE_ARRAY);

            // Add status other by type
            if ('type' === $col && array_key_exists($request->get(self::KEY_SEARCH), $this->other)) {
                array_push($data, $this->other[$request->get(self::KEY_SEARCH)]);
            }

            return $app->json($data);
        }
        catch (HttpException $e) {
            $app->getMonolog()->addWarning($e);
        }
    }

    public function postUpdateAction(Application $app, Request $request)
    {
        $em = $app->getEntityManager();
        try {
            $queue = $em->find(Queue::class, ($id = $request->get('id')));

            $queue->setStatusId(filter_var($request->get('status_id'), FILTER_SANITIZE_NUMBER_INT));
            $queue->setStatusOtherText(filter_var($request->get('status_other_text'), FILTER_SANITIZE_STRING));

            $em->persist($queue);
            $em->flush();

            return $app->json(sprintf('success'));
        }
        catch (HttpException $e) {
            return $app->json(['message' => $e->getMessage()], $e->getStatusCode());
        }
    }
}
