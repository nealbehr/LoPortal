<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 6/6/15
 * Time: 6:20 PM
 */
namespace LO\Common\Email\Request;

class PropertyApprovalDenialTest extends \PHPUnit_Framework_TestCase{
    public function testGetSubjectIsString(){
        $this->assertTrue(is_string((new PropertyApprovalDenial(""))->getSubject()));
    }

    public function testGetTemplateName(){
        $this->assertTrue(is_string((new PropertyApprovalDenial(""))->getTemplateName()));
    }

    public function testGetTemplateVars(){
        $this->assertTrue(is_array((new PropertyApprovalDenial(""))->getTemplateVars()));
    }
} 