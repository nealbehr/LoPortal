<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 3/12/15
 * Time: 4:05 PM
 */

namespace LO\Controller;


use LO\Application;
use LO\Form\UserAdminForm;
use LO\Model\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Form\Form;

class Admin {
    const USER_LIMIT    = 20;

    const KEY_SEARCH    = 'filterValue';
    const KEY_PAGE      = 'page';
    const KEY_SORT      = 'sort';
    const KEY_DIRECTION = 'direction';
    const DEFAULT_SORT_FIELD_NAME = 'id';
    const DEFAULT_SORT_DIRECTION  = 'asc';

    const KEY_STATE           = 'state';

    /** @var array  */
    private $errors = [];

    protected function getOrderDirection($direction){
        return in_array(strtolower($direction), ['asc', 'desc'])? $direction: self::DEFAULT_SORT_DIRECTION;
    }

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
        /** @var User $item */
        foreach($pagination->getItems() as $item){
            $items[] = $item->getPublicInfo();
        }

        return $app->json([
            'pagination'    => $pagination->getPaginationData(),
            'keySearch'     => self::KEY_SEARCH,
            'keyState'      => self::KEY_STATE,
            'keySort'       => self::KEY_SORT,
            'keyDirection'  => self::KEY_DIRECTION,
            'users'         => $items,
        ]);
    }

    public function getRolesAction(Application $app){
        return $app->json(User::getAllowedRoles());
    }

    public function addUserAction(Application $app, Request $request){
        try{
            $user = new User();

            $this->validateAndSaveUser($app, $request, $user);

            return $app->json(['id' => $user->getId()]);
        }catch(HttpException $e){
            $app->getMonolog()->addError($e);
            $this->errors['message'] = $e->getMessage();
            return $app->json($this->errors, $e->getStatusCode());
        }
    }

    public function updateUserAction(Application $app, Request $request, $id){
        try{
            /** @var User $user */
            $user = $app->getEntityManager()->getRepository(User::class)->find($id);

            if(!$user){
                throw new BadRequestHttpException("User not found.");
            }

            if($user->getId() == $app->user()->getId()){
                throw new BadRequestHttpException("You can't edit self.");
            }

            $this->validateAndSaveUser($app, $request, $user);

            return $app->json('success');
        }catch(HttpException $e){
            $app->getMonolog()->addWarning($e);
            $this->errors['message'] = $e->getMessage();
            return $app->json($this->errors, $e->getStatusCode());
        }
    }

    public function deleteAction(Application $app, Request $request, $id){
        try {
            /** @var User $user */
            $user = $app->getEntityManager()->getRepository(User::class)->find($id);

            if (!$user) {
                throw new BadRequestHttpException("User not found.");
            }

            if ($user->getId() == $app->user()->getId()) {
                throw new BadRequestHttpException("You remove self.");
            }

            $app->getEntityManager()->remove($user);
            $app->getEntityManager()->flush();

            return $app->json('success');
        }catch(HttpException $e){
            $app->getMonolog()->addWarning($e);
            $this->errors['message'] = $e->getMessage();
            return $app->json($this->errors, $e->getStatusCode());
        }
    }

    private function setErrorsForm(Form $form){
        $errors = [];
        foreach($form as $child){
            if($child->getErrors()->count() > 0){
                $errors[] = str_replace("ERROR: ", "", (string)$child->getErrors());
            }
        }

        $this->errors['form_errors'] = $errors;
    }

    /**
     * @param Application $app
     * @param Request $request
     * @param User $user
     * @return void
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     */
    private function validateAndSaveUser(Application $app, Request $request, User $user){
        $requestUser = $request->request->get('user');
        $formOptions = [
            'validation_groups' => ['Default'],
        ];
        if(!$user || (isset($requestUser['email']) && $user->getEmail() != $requestUser['email'])){//remove uniq constrain
            $formOptions['validation_groups'] = array_merge($formOptions['validation_groups'], ["New"]);
        }

        $form = $app->getFormFactory()->create(new UserAdminForm(), $user, $formOptions);

        $form->submit($this->removeExtraFields($requestUser, $form));

        if(!$form->isValid()){
            $this->setErrorsForm($form);
            throw new BadRequestHttpException('User info not valid.');
        }

        $user->setSalt($user->generateSalt());
        $user->setPassword($app->encodePassword($user, substr(md5(time()), 0, 10)));

        $app->getEntityManager()->persist($user);
        $app->getEntityManager()->flush();
    }

    private function removeExtraFields($requestData, $form){
        return array_intersect_key($requestData, $form->all());
    }

    private function getUserList(Request $request, Application $app){
        $q = $app->getEntityManager()->createQueryBuilder()
            ->select('u')
            ->from(User::class, 'u')
            ->where('u.state = :active')
            ->setMaxResults(static::USER_LIMIT)
            ->orderBy($this->getOrderKey($request->query->get(self::KEY_SORT)), $this->getOrderDirection($request->query->get(self::KEY_DIRECTION)))
            ->setParameter('active', User::STATE_ACTIVE)
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
        $allowFields = ['id', 'first_name', 'last_name'];

        return 'u.'.(in_array($id, $allowFields)? $id: self::DEFAULT_SORT_FIELD_NAME);
    }

} 