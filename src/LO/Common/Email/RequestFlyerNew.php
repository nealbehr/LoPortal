<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 4/22/15
 * Time: 7:28 PM
 */

namespace LO\Common\Email;

use LO\Application;
use LO\Model\Entity\Queue;
use LO\Model\Entity\Realtor;
use LO\Model\Entity\RequestFlyer;

class RequestFlyerNew extends Base{
    private $app;
    private $queue;
    private $realtor;
    private $requestFlyer;

    public function __construct(Application $app, $source, Queue $queue, Realtor $realtor, RequestFlyer $requestFlyer){
        parent::__construct($app->getSes(), $source);

        $this->app     = $app;
        $this->queue   = $queue;
        $this->realtor = $realtor;
        $this->requestFlyer = $requestFlyer;
    }

    protected function getSubject(){
        return "Listing Flyer Request";
    }

    protected function getBody(){
        return $this->app->getTwig()->render("email.request.flyer.new.twig", [
            'queue' => $this->queue,
            'realtor' => $this->realtor,
            'requestFlyer' => $this->requestFlyer,
        ]);
    }

}