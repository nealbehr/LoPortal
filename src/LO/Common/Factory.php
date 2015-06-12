<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 6/12/15
 * Time: 6:54 PM
 */

namespace LO\Common;

use LO\Model\Entity\Token;

class Factory {
    /**
     * @return Token
     */
    public function token(){
        return new Token();
    }
} 