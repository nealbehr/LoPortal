<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 4/11/15
 * Time: 1:49 PM
 */

namespace LO\Common\Email;


use LO\Application;

class NewPassword extends Base{
    private $app;
    private $password;

    public function __construct(Application $app, $source, $password){
        parent::__construct($app->getSes(), $source);

        $this->app      = $app;
        $this->password = $password;
    }

    protected function getSubject(){
        return "New password";
    }

    protected function getBody(){
        return $this->app->getTwig()->render("password.new.body.twig", [
            'password' => $this->password,
        ]);
    }

} 