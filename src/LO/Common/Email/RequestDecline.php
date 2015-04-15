<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 4/15/15
 * Time: 11:02 AM
 */

namespace LO\Common\Email;

use LO\Application;
use LO\Model\Entity\Queue;

class RequestDecline extends Base{
    private $app;
    private $queue;

    public function __construct(Application $app, $source, Queue $queue){
        parent::__construct($app->getSes(), $source);

        $this->app   = $app;
        $this->queue = $queue;
    }

    protected function getSubject(){
        return "Request declined.";
    }

    protected function getBody(){
        return $this->app->getTwig()->render("request.email.decline.twig", [
            'queue' => $this->queue,
        ]);
    }

} 