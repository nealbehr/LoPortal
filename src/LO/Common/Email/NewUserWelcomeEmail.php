<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 4/11/15
 * Time: 1:49 PM
 */

namespace LO\Common\Email;


use LO\Application;

class NewUserWelcomeEmail extends Base
{
    private $app;
    private $password;
    private $email;

    public function __construct(Application $app, $source, $password, $email)
    {
        parent::__construct($app->getSes(), $source);

        $this->app = $app;
        $this->password = $password;
        $this->email = $email;
    }

    protected function getSubject()
    {
        return "Welcome to the REX HomeBuyer Loan Officer Portal";
    }

    protected function getBody()
    {
        return $this->app->getTwig()->render("emai.user.welcome.twig", [
            'password' => $this->password,
            'email' => $this->email,
        ]);
    }

} 