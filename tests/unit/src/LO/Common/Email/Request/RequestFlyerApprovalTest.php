<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 6/6/15
 * Time: 6:25 PM
 */
namespace LO\Common\Email\Request;

use LO\Model\Entity\Realtor;
use LO\Model\Entity\Queue;

class RequestFlyerApprovalTest extends \PHPUnit_Framework_TestCase{
    public function testGetSubjectIsString(){
        $this->assertTrue(is_string((new RequestFlyerApproval(new Realtor(), new Queue(), ""))->getSubject()));
    }

    public function testGetTemplateName(){
        $this->assertTrue(is_string((new RequestFlyerApproval(new Realtor(), new Queue(), ""))->getTemplateName()));
    }

    public function testGetTemplateVars(){
        $this->assertTrue(is_array((new RequestFlyerApproval(new Realtor(), new Queue(), ""))->getTemplateVars()));
    }
} 