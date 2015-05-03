<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 5/3/15
 * Time: 9:25 AM
 */

namespace LO\Common\Email\Request;


use LO\Model\Entity\Realtor;
use LO\Model\Entity\RequestFlyer;

class RequestFlyerApproval implements RequestInterface{
    private $realtor;
    private $requestFlyer;
    private $url;

    public function __construct(Realtor $realtor, RequestFlyer $requestFlyer, $url) {
        $this->realtor      = $realtor;
        $this->requestFlyer = $requestFlyer;
        $this->url          = $url;
    }

    public function getSubject(){
        return "Listing Flyer Request has been Approved";
    }

    public function getTemplateName(){
        return "email.request.flyer.approved.twig";
    }

    public function getTemplateVars(){
        return [
            'realtor'      => $this->realtor,
            'requestFlyer' => $this->requestFlyer,
            'url'          => $this->url,
        ];
    }
} 