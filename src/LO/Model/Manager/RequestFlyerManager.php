<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 4/17/15
 * Time: 10:50 AM
 */

namespace LO\Model\Manager;


use LO\Model\Entity\RequestFlyer;

/**
 * Class RequestFlyerManager
 * @package LO\Model\Manager
 */
class RequestFlyerManager extends Base {

    /**
     * Get Request flyer by id
     *
     * @param $flyerID
     * @return null|RequestFlyer
     */
    public function getById($flyerID){
        return $this->getApp()
            ->getEntityManager()
            ->getRepository(RequestFlyer::CLASS_NAME)
            ->find($flyerID);
    }
} 