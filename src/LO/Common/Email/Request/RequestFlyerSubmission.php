<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 5/2/15
 * Time: 3:41 PM
 */

namespace LO\Common\Email\Request;


use LO\Model\Entity\Queue;
use LO\Model\Entity\Realtor;

class RequestFlyerSubmission implements RequestInterface{
    private $realtor;
    private $queue;

    public function __construct(Realtor $realtor, Queue $queue) {
        $this->realtor = $realtor;
        $this->queue = $queue;
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
            'queue' => $this->queue,
        ];
    }
}