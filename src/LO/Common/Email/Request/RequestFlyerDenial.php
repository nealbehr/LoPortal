<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 5/3/15
 * Time: 9:52 AM
 */

namespace LO\Common\Email\Request;


use LO\Model\Entity\Realtor;
use LO\Model\Entity\RequestFlyer;

class RequestFlyerDenial implements RequestInterface{
    private $realtor;
    private $requestFlyer;
    private $email;

    public function __construct(Realtor $realtor, RequestFlyer $requestFlyer, $email) {
        $this->realtor      = $realtor;
        $this->requestFlyer = $requestFlyer;
        $this->email        = $email;
    }

    public function getSubject(){
        return "Listing Flyer Request has been Declined";
    }

    public function getTemplateName(){
        return "email.request.flyer.denial.twig";
    }

    public function getTemplateVars(){
        return [
            'realtor'      => $this->realtor,
            'requestFlyer' => $this->requestFlyer,
            'email'        => $this->email,
        ];
    }
} 