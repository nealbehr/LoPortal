<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 5/2/15
 * Time: 3:08 PM
 */

namespace LO\Common\Email\Request;


class PropertyApprovalAccept implements RequestInterface{
    private $url;

    public function __construct($url){
        $this->url = $url;
    }

    public function getSubject(){
        return "Property Approval Request has been Approved";
    }

    public function getTemplateName(){
        return "email.property.approval.accept.twig";
    }

    public function getTemplateVars(){
        return [
            'url' => $this->url,
        ];
    }

} 