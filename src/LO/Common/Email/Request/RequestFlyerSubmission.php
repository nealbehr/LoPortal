<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 5/2/15
 * Time: 3:41 PM
 */

namespace LO\Common\Email\Request;


use LO\Model\Entity\Realtor;
use LO\Model\Entity\RequestFlyer;

class RequestFlyerSubmission implements RequestInterface{
    private $realtor;
    private $requestFlyer;

    public function __construct(Realtor $realtor, RequestFlyer $requestFlyer) {
        $this->realtor = $realtor;
        $this->requestFlyer = $requestFlyer;
    }

    public function getSubject(){
        return "Listing Flyer Request";
    }

    public function getTemplateName(){
        return "email.request.flyer.new.twig";
    }

    public function getTemplateVars(){
        return [
            'realtor' => $this->realtor,
            'requestFlyer' => $this->requestFlyer,
        ];
    }
}