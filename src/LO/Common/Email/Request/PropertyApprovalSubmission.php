<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 5/2/15
 * Time: 2:42 PM
 */

namespace LO\Common\Email\Request;


class PropertyApprovalSubmission implements RequestInterface{
    public function getSubject(){
        return "Property Approval Request";
    }

    public function getTemplateName(){
        return "email.property.approval.new.twig";
    }

    public function getTemplateVars(){
        return [];
    }
} 