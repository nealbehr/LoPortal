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

class UserManager extends Base{
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

    public function findByEmailPassword($email, $password){
        $user = $this->findByEmail($email);

        if(!$user || !$this->getApp()['security.encoder_factory']->getEncoder($user)->isPasswordValid($user->getPassword(), $password, $user->getSalt())){
            return false;
        }

        return $user;
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
            $errors = [];
            foreach($form as $child){
                if($child->getErrors()->count() > 0){
                    $errors[] = str_replace("ERROR: ", "", (string)$child->getErrors());
                }
            }

            return $errors;
        }

        $this->getApp()->getEntityManager()->persist($user);
        $this->getApp()->getEntityManager()->flush();

        return [];
    }

    private function removeExtraFields($requestData, $form){
        return array_intersect_key($requestData, $form->all());
    }

} 