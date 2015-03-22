<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 3/12/15
 * Time: 7:08 PM
 */

namespace LO\Controller;

use LO\Application;
use LO\Model\Entity\User;

class Dashboard {
    public function indexAction(Application $app){

        return $app->json(['user' => $this->getUserInfo4TopMenu($app->user())]);
    }


    private function getUserInfo4TopMenu(User $user){
        return [
            'full_name' => (string)$user,
            'picture'   => $user->getPicture(),
        ];
    }
} 