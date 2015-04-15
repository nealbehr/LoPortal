<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 4/15/15
 * Time: 11:09 AM
 */

namespace LO\Common\Email;

use LO\Application;
use LO\Model\Entity\Queue;

class RequestApprove extends Base{
    private $app;
    private $queue;

    public function __construct(Application $app, $source, Queue $queue){
        parent::__construct($app->getSes(), $source);

        $this->app   = $app;
        $this->queue = $queue;
    }

    protected function getSubject(){
        return "Request approved.";
    }

    protected function getBody(){
        return $this->app->getTwig()->render("request.email.approve.twig", [
            'queue' => $this->queue,
        ]);
    }

}