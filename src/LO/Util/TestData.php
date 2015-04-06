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
                 ->setSalt($user->generateSalt())
                 ->addRole(User::ROLE_USER)
            ;
            $user->setPassword($this->app->encodePassword($user, '123456'));

            $this->app->getEntityManager()->persist($user);
        }

        $this->app->getEntityManager()->flush();
    }

    public function createMe(){
        $user = new User();
        $user->setEmail('s.samoilenko@gmail.com')
             ->setSalt($user->generateSalt())
             ->setPassword($this->app->encodePassword($user, '123456'))
             ->addRole(User::ROLE_USER)
        ;

        $this->app->getEntityManager()->persist($user);
        $this->app->getEntityManager()->flush();
    }

    public function createAdmin(){
        $user = new User();
        $user->setEmail('admin@1rex.com')
             ->setFirstName('AdminFirst')
             ->setLastName('AdminLast')
             ->setSalt($user->generateSalt())
             ->setPassword($this->app->encodePassword($user, '123456'))
             ->addRole(User::ROLE_ADMIN)
        ;

        $this->app->getEntityManager()->persist($user);
        $this->app->getEntityManager()->flush();
    }

    public function resetPasswordAllUsers(){
        $user = new User();

        $this->app->getEntityManager()->createQueryBuilder()
            ->update(User::class, 'u')
            ->set('u.salt', '?1')
            ->set('u.password', '?2')
            ->setParameter(1, $user->getSalt())
            ->setParameter(2, $this->app->encodePassword($user, '123456'))
        ->getQuery()
        ->execute();
    }

    public function createAndrey(){
        $user = new User();
        $user->setEmail('andriy.lypovskiy@appsorama.com')
             ->setSalt($user->generateSalt())
             ->setPassword($this->app->encodePassword($user, '123456'))
             ->addRole(User::ROLE_USER)
        ;

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

    public function getImportUsers($filename){
        if (($handle = fopen($filename, "r")) === FALSE) {
            throw new \Exception(sprintf("File \'%s\' not found.", $filename));
        }

        $result = [];

        while (($data = fgetcsv($handle, null, ";")) !== FALSE){
            if(empty($data[11])){
                $this->app->getMonolog()->addError('USER_NOT_FOUND: '.implode(';', $data));
                continue;
            }

            unset($data[4], $data[12]);

            $data = array_map(function($el){
                return '"'.$el.'"';
            }, $data);

            $data[] = '"$2y$10$6f8494e32b77d03dd13aeua9.lXIhP.WOmvJGDGhWYVQUUILCnDiS"';
            $data[] = '"6f8494e32b77d03dd13ae2fc3b4fb51f"';
            $data[] = user::STATE_ACTIVE;
            $data[] = '"a:1:{i:0;s:9:\"ROLE_USER\";}"';
            $result[] = '('.implode(',', $data).')';
        }
        fclose($handle);

        return ["insert into users(first_name, last_name, title, account_name, street, city,
                                      province, zip_code, phone, mobile, email, nmls, pmp, territory,
                                      sales_director, password, salt, state, roles) VALUES " => $result];
    }
} 