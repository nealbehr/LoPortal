<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 4/8/15
 * Time: 5:41 PM
 */
namespace LO\Controller;

use LO\Common\Message;

class RequestBaseController
{
    private $message;

    public function __construct()
    {
        $this->message = new Message();
    }

    protected function getMessage()
    {
        return $this->message;
    }
}
