<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 6/12/15
 * Time: 6:54 PM
 */

namespace LO\Common;

use LO\Application;
use LO\Common\Email\RecoveryPassword;
use LO\Model\Entity\Token;
use LO\Model\Entity\RecoveryPassword as RecoveryPasswordEntity;
use Curl\Curl;
use LO\Model\Manager\QueueManager;

class Factory {
    /**
     * @return Token
     */
    public function token(){
        return new Token();
    }

    /**
     * @param Application $app
     * @param $source
     * @param $recoveryPassword
     * @return RecoveryPassword
     */
    public function recoveryPassword(Application $app, $source, RecoveryPasswordEntity $recoveryPassword){
        return new RecoveryPassword($app, $source, $recoveryPassword);
    }

    /**
     * @return Curl
     */
    public function curl(){
        return new Curl();
    }

    /**
     * @param Application $app
     * @return QueueManager
     */
    public function queueManager(Application $app){
        return new QueueManager($app);
    }

} 