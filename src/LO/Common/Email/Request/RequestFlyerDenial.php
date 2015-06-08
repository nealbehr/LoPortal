<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 5/3/15
 * Time: 9:52 AM
 */

namespace LO\Common\Email\Request;


use LO\Model\Entity\Queue;
use LO\Model\Entity\Realtor;

class RequestFlyerDenial implements RequestInterface{
    private $realtor;
    private $queue;
    private $email;

    public function __construct(Realtor $realtor, Queue $queue, $email) {
        $this->realtor      = $realtor;
        $this->queue        = $queue;
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
            'queue'        => $this->queue,
            'email'        => $this->email,
        ];
    }
} 