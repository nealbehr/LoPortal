<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 3/13/15
 * Time: 2:11 PM
 */

namespace LO\Model\Manager;

use Doctrine\ORM\Query\Expr;
use LO\Form\UserForm;
use LO\Model\Entity\Token;
use LO\Model\Entity\User as EntityUser;
use LO\Model\Entity\User;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use LO\Traits\GetFormErrors;

class UserManager extends Base{
    use GetFormErrors;

    public function findByToken($token){
        return $this->getApp()->getEntityManager()
                    ->getRepository(EntityUser::class)
                    ->createQueryBuilder('u')
                    ->select('u')
                    ->join(Token::class, 't', Expr\Join::WITH, 'u.id = t.user_id')
                    ->where('t.hash = :token')
                    ->andWhere('t.expiration_time > :expireTime')
                    ->setParameter('token', $token)
                    ->setParameter('expireTime', new \DateTime())
                    ->setMaxResults(1)
                    ->getQuery()
                    ->getOneOrNullResult()
        ;
    }

    /**
     * @param $email
     * @return null|EntityUser
     */
    public function findByEmail($email){
        return $this->getApp()
                     ->getEntityManager()
                     ->getRepository(EntityUser::class)
                     ->findOneBy(['email' => $email])
        ;
    }

    /**
     * @param Request $request
     * @param User $user
     * @param UserForm $userForm
     * @return array|bool
     */
    public function validateAndSaveUser(Request $request, User $user, UserForm $userForm){
        $requestUser = $request->request->get('user');
        $formOptions = [
            'validation_groups' => ['Default'],
        ];

        if(!$user || (isset($requestUser['email']) && $user->getEmail() != $requestUser['email'])){//remove uniq constrain
            $formOptions['validation_groups'] = array_merge($formOptions['validation_groups'], ["New"]);
        }

        $form = $this->getApp()->getFormFactory()->create($userForm, $user, $formOptions);

        $form->submit($this->removeExtraFields($requestUser, $form));

        if(!$form->isValid()){
            return $this->getFormErrors($form);
        }

        $this->getApp()->getEntityManager()->persist($user);
        $this->getApp()->getEntityManager()->flush();

        return [];
    }
}