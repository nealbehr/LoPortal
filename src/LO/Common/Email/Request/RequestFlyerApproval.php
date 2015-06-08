<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 5/3/15
 * Time: 9:25 AM
 */

namespace LO\Common\Email\Request;


use LO\Model\Entity\Queue;
use LO\Model\Entity\Realtor;

class RequestFlyerApproval implements RequestInterface{
    private $realtor;
    private $queue;
    private $url;

    public function __construct(Realtor $realtor, Queue $queue, $url) {
        $this->realtor      = $realtor;
        $this->queue        = $queue;
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
            'queue'        => $this->queue,
            'url'          => $this->url,
        ];
    }
} 