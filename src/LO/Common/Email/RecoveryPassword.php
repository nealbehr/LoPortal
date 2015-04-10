<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 4/10/15
 * Time: 8:13 PM
 */

namespace LO\Common\Email;

use LO\Application;
use LO\Model\Entity\RecoveryPassword as RecoveryPasswordEntity;

class RecoveryPassword extends Base{
    private $app;
    private $recoveryPassword;

    public function __construct(Application $app, $source, RecoveryPasswordEntity $recoveryPassword){
        parent::__construct($app->getSes(), $source);

        $this->app = $app;
        $this->recoveryPassword = $recoveryPassword;
    }


    protected function getSubject(){
        return "recovery password";
    }

    protected function getBody(){
        return $this->app->getTwig()->render("recovery.password.body.twig", [
            'signature' => $this->recoveryPassword->getSignature(),
            'id' => $this->recoveryPassword->getId(),
        ]);
    }


} 