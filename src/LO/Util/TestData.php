<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 3/14/15
 * Time: 7:49 PM
 */

namespace LO\Util;


use LO\Application;
use LO\Model\Entity\User;

class TestData {
    private $app;

    public function __construct(Application $app){
        $this->app = $app;
    }

    public function generateUsers(){
        $domains = ['com', 'net', 'ua', 'uk', 'fr'];
        foreach(range(1, 40) as $v){
            $user = new User();
            $user->setEmail($this->generateRandomString(mt_rand(5, 7)).'@'.$this->generateRandomString(mt_rand(4, 7)).'.'.$domains[mt_rand(0, count($domains) - 1)]  )
                 ->setSalt($user->generateSalt());
            $user->setPassword($this->app->encodePassword($user, '123456'));

            $this->app->getEntityManager()->persist($user);
        }

        $this->app->getEntityManager()->flush();
    }

    public function createMe(){
        $user = new User();
        $user->setEmail('s.samoilenko@gmail.com')
             ->setSalt($user->generateSalt())
             ->setPassword($this->app->encodePassword($user, '123456'));

        $this->app->getEntityManager()->persist($user);
        $this->app->getEntityManager()->flush();
    }

    public function generateRandomString($length = 10) {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        return $randomString;
    }
} 