<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 4/7/15
 * Time: 3:30 PM
 */

namespace LO\Controller\Admin;

use LO\Application;
use LO\Common\Email\NewUserWelcomeEmail;
use LO\Common\Email\ResetPassword;
use LO\Form\UserAdminFormType;
use LO\Model\Entity\Lender;
use LO\Model\Manager\UserManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use LO\Model\Entity\User as EntityUser;

class AdminUserController extends Base {
    const USER_LIMIT    = 20;

    const DEFAULT_SORT_FIELD_NAME = 'id';
    const DEFAULT_SORT_DIRECTION  = 'asc';

    const KEY_STATE           = 'state';

    /** @var array  */
    private $errors = [];


    public function getUsersAction(Application $app, Request $request){
        /** @var \Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination $pagination */
        $pagination = $app->getPaginator()->paginate(
            $this->getUserList($request, $app),
            (int) $request->get(self::KEY_PAGE, 1),
            self::USER_LIMIT,
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
        /** @var EntityUser $item */
        foreach($pagination->getItems() as $item){
            $tmp = $item->getPublicInfo();
            $tmp['password'] = $item->getPassword();
            $items[] = $tmp;
        }

        return $app->json([
            'pagination'    => $pagination->getPaginationData(),
            'keySearch'     => self::KEY_SEARCH,
            'keyState'      => self::KEY_STATE,
            'keySort'       => self::KEY_SORT,
            'keyDirection'  => self::KEY_DIRECTION,
            'users'         => $items,
            'defDirection'  => self::DEFAULT_SORT_DIRECTION,
            'defField'      => self::DEFAULT_SORT_FIELD_NAME,
        ]);
    }

    public function getRolesAction(Application $app){
        return $app->json(EntityUser::getAllowedRoles());
    }

    public function addUserAction(Application $app, Request $request){
        try{
            $app->getEntityManager()->beginTransaction();
            $user = new EntityUser();
            $password = $user->generatePassword();
            $user->setSalt($user->generateSalt())
                 ->setPassword($app->encodePassword($user, $password));

            $this->getRequestLender($app, $request, $user);
            $errors = (new UserManager($app))->validateAndSaveUser($request, $user, new UserAdminFormType($app->getS3()), 'POST');

            if(count($errors) > 0){
                $this->setErrorsForm($errors);
                throw new BadRequestHttpException('User info not valid.');
            }

//            // Disable a welcome email
//            (new NewUserWelcomeEmail($app, $app->getConfigByName('amazon', 'ses', 'source'), $password, $user->getEmail()))
//                ->setDestinationList($user->getEmail())
//                ->send();

            $app->getEntityManager()->commit();

            return $app->json(['id' => $user->getId()]);
        }catch(HttpException $e){
            $app->getEntityManager()->rollback();
            $app->getMonolog()->addError($e);
            $this->errors['message'] = $e->getMessage();
            return $app->json($this->errors, $e->getStatusCode());
        }
    }

    public function updateUserAction(Application $app, Request $request, $id){
        try{
            $user = $this->getUser($app, $id, "You can't edit self.");
            $this->getRequestLender($app, $request, $user);
            $errors = (new UserManager($app))->validateAndSaveUser(
                $request,
                $user,
                new UserAdminFormType($app->getS3())
            );

            if(count($errors) > 0){
                $this->setErrorsForm($errors);
                throw new BadRequestHttpException('User info not valid.');
            }

            return $app->json('success');
        }catch(HttpException $e){
            $app->getMonolog()->addWarning($e);
            $this->errors['message'] = $e->getMessage();
            return $app->json($this->errors, $e->getStatusCode());
        }
    }

    public function deleteAction(Application $app, $id){
        try {
            $user = $this->getUser($app, $id, 'You can not remove self.');
            $user->setDeleted('1')->setEmail($user->getEmail().'-'.strtotime('now').'-deleted');
            $app->getEntityManager()->persist($user);
            $app->getEntityManager()->flush();

            return $app->json('success');
        }catch(HttpException $e){
            $app->getMonolog()->addWarning($e);
            $this->errors['message'] = $e->getMessage();
            return $app->json($this->errors, $e->getStatusCode());
        }
    }

    public function resetPasswordAction(Application $app, $userId){
        try{
            $app->getEntityManager()->beginTransaction();
            $user = $this->getUser($app, $userId, "You can not edit self.");

            $password = $user->generatePassword();

            $user->setSalt($user->generateSalt())
                 ->setPassword($app->encodePassword($user, $password));

            $app->getEntityManager()->persist($user);
            $app->getEntityManager()->flush();

            (new ResetPassword($app, $app->getConfigByName('amazon', 'ses', 'source'), $password))
                ->setDestinationList($user->getEmail())
                ->send();

            $app->getEntityManager()->commit();

            return $app->json("success");
        }catch(HttpException $e){
            $app->getEntityManager()->rollback();
            return $app->json(['message' => $e->getMessage()], $e->getStatusCode());
        }
    }

    private function setErrorsForm(array $errors){
        $this->errors['form_errors'] = $errors;

        return $this;
    }

    private function getUser(Application $app, $userId, $errorMessage){
        /** @var EntityUser $user */
        $user = $app->getEntityManager()->getRepository(EntityUser::class)->find($userId);

        if(!$user){
            throw new BadRequestHttpException("User not found.");
        }

        if($user->getId() == $app->getSecurityTokenStorage()->getToken()->getUser()->getId()){
            throw new BadRequestHttpException($errorMessage);
        }

        return $user;
    }

    private function getUserList(Request $request, Application $app){
        $q = $app->getEntityManager()->createQueryBuilder()
            ->select('u')
            ->from(EntityUser::class, 'u')
            ->where('u.state = :active')
            ->andWhere("u.deleted = '0'")
            ->setMaxResults(static::USER_LIMIT)
            ->orderBy($this->getOrderKey($request->query->get(self::KEY_SORT)), $this->getOrderDirection($request->query->get(self::KEY_DIRECTION), self::DEFAULT_SORT_DIRECTION))
            ->setParameter('active', EntityUser::STATE_ACTIVE)
        ;

        if($request->get(self::KEY_SEARCH)){
            $q->andWhere(
                $app->getEntityManager()->createQueryBuilder()->expr()->orX(
                    $app->getEntityManager()->createQueryBuilder()->expr()->like("LOWER(u.first_name)", ':param'),
                    $app->getEntityManager()->createQueryBuilder()->expr()->like("LOWER(u.last_name)", ':param'),
                    $app->getEntityManager()->createQueryBuilder()->expr()->like("LOWER(u.email)", ':param'),
                    $app->getEntityManager()->createQueryBuilder()->expr()->like("LOWER(u.phone)", ':param'),
                    $app->getEntityManager()->createQueryBuilder()->expr()->like("LOWER(u.roles)", ':param')
                )
            )
                ->setParameter('param', '%'.strtolower($request->get(self::KEY_SEARCH)).'%')
            ;
        }

        return $q->getQuery();
    }

    private function getOrderKey($id){
        $allowFields = ['id', 'first_name', 'last_name', 'email', 'created_at'];

        return 'u.'.(in_array($id, $allowFields)? $id: self::DEFAULT_SORT_FIELD_NAME);
    }

    /**
     * @param Application $app
     * @param Request $request
     * @param $user
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     */
    private function getRequestLender(Application $app, Request $request, $user)
    {
        $requestUser = $request->request->get('user');
        $requestLender = $requestUser['lender'];
        if ($requestLender) {
            $lender = $app->getEntityManager()->getRepository(Lender::class)->find($requestLender['id']);
            if ($lender) {
                $user->setLender($lender);
            } else {
                throw new BadRequestHttpException('Lender info not valid.');
            }
        }
    }
} 