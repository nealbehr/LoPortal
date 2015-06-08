<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 5/2/15
 * Time: 2:09 PM
 */

namespace LO\Common\Email\Request;


use LO\Application;
use LO\Model\Entity\Queue;
use LO\Common\Email\Base;

class RequestChangeStatus extends Base{
    private $app;
    private $queue;
    private $request;

    public function __construct(Application $app, Queue $queue, RequestInterface $request){
        parent::__construct($app->getSes(), $app->getConfigByName('amazon', 'ses', 'source'));

        $this->app   = $app;
        $this->queue = $queue;
        $this->request = $request;

        $destinationList = [$queue->getUser()->getEmail()];
        if($queue->getUser()->getSalesDirectorEmail()){
            $destinationList[] = $queue->getUser()->getSalesDirectorEmail();
        }

        $this->setDestinationList(array_merge($destinationList, $app->getConfigByName('firstrex', 'additional.emails')));
    }

    public function send(){
        if($this->app->isDebug()){
            return;
        }

        parent::send();
    }

    protected function getSubject(){
        return $this->request->getSubject();
    }

    protected function getBody(){
        return $this->app->getTwig()->render(
            $this->request->getTemplateName(),
            array_merge(['queue' => $this->queue,], $this->request->getTemplateVars())
        );
    }

}