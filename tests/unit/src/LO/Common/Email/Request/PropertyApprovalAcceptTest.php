<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 6/6/15
 * Time: 6:12 PM
 */
namespace LO\Common\Email\Request;

class PropertyApprovalAcceptTest extends \PHPUnit_Framework_TestCase{
    public function testGetSubjectIsString(){
        $this->assertTrue(is_string((new PropertyApprovalAccept(""))->getSubject()));
    }

    public function testGetTemplateName(){
        $this->assertTrue(is_string((new PropertyApprovalAccept(""))->getTemplateName()));
    }

    public function testGetTemplateVars(){
        $this->assertTrue(is_array((new PropertyApprovalAccept(""))->getTemplateVars()));
    }

} 