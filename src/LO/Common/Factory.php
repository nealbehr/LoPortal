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
    public function recoveryPassword(Application $app, $source, $recoveryPassword){
        return new RecoveryPassword($app, $source, $recoveryPassword);
    }
} 