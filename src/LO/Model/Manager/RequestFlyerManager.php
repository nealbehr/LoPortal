<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 4/17/15
 * Time: 10:50 AM
 */

namespace LO\Model\Manager;


use LO\Model\Entity\RequestFlyer;

class RequestFlyerManager extends Base{
    public function getById($id){
        $this->getApp()
            ->getEntityManager()
            ->getRepository(RequestFlyer::class)
            ->find($id);
    }
} 