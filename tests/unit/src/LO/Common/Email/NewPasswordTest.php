<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 6/6/15
 * Time: 5:11 PM
 */
namespace LO\Common\Email;

use \Mockery as m;

class NewPasswordTest extends \PHPUnit_Framework_TestCase{
    public function testSendWithoutDestinationList(){
        $this->setExpectedException('\Exception');

        $app = m::mock('\LO\Application');

        $email = new NewUserWelcomeEmail($app, "", "", "testing@appsorama.com");
        $email->send();
    }
}