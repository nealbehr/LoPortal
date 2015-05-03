<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 5/2/15
 * Time: 3:28 PM
 */

namespace LO\Common\Email\Request;


class PropertyApprovalDenial implements RequestInterface{
    private $email;

    public function __construct($email){
        $this->email = $email;
    }

    public function getSubject(){
        return "Property Approval Request has been Declined";
    }

    public function getTemplateName(){
        return "email.property.approval.denial.twig";
    }

    public function getTemplateVars(){
        return [
            'email' => $this->email,
        ];
    }

} 