<?php namespace LO\Controller\Admin;

use LO\Application;
use Symfony\Component\HttpFoundation\Request;
use LO\Traits\GetFormErrors;
use Symfony\Component\HttpKernel\Exception\HttpException;
use LO\Model\Entity\Status;
use \Doctrine\ORM\Query;

class StatusController extends Base
{
    use GetFormErrors;

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

    public function getAllByTypeAction(Application $app, Request $request) {
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
}
