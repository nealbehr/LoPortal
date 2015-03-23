<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 3/23/15
 * Time: 6:03 PM
 */

namespace LO\Controller;


use LO\Application;

class User {
    public function getByIdAction(Application $app, $id){
        if("me" == $id || $app->user()->getId() == $id){
            return $app->json($app->user()->getPublicInfo());
        }
    }
} 