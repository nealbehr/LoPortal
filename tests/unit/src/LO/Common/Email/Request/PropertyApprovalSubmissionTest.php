<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 6/6/15
 * Time: 6:19 PM
 */
namespace LO\Common\Email\Request;

class PropertyApprovalSubmissionTest extends \PHPUnit_Framework_TestCase{
    public function testGetSubjectIsString(){
        $this->assertTrue(is_string((new PropertyApprovalSubmission(""))->getSubject()));
    }

    public function testGetTemplateName(){
        $this->assertTrue(is_string((new PropertyApprovalSubmission(""))->getTemplateName()));
    }

    public function testGetTemplateVars(){
        $this->assertTrue(is_array((new PropertyApprovalSubmission(""))->getTemplateVars()));
    }
}