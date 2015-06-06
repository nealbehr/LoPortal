<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 6/6/15
 * Time: 6:28 PM
 */
namespace LO\Common\Email\Request;

use LO\Model\Entity\Realtor;
use LO\Model\Entity\RequestFlyer;

class RequestFlyerDenialTest extends \PHPUnit_Framework_TestCase{
    public function testGetSubjectIsString(){
        $this->assertTrue(is_string((new RequestFlyerDenial(new Realtor(), new RequestFlyer(), ""))->getSubject()));
    }

    public function testGetTemplateName(){
        $this->assertTrue(is_string((new RequestFlyerDenial(new Realtor(), new RequestFlyer(), ""))->getTemplateName()));
    }

    public function testGetTemplateVars(){
        $this->assertTrue(is_array((new RequestFlyerDenial(new Realtor(), new RequestFlyer(), ""))->getTemplateVars()));
    }
} 