<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 4/7/15
 * Time: 4:10 PM
 */

namespace LO\Controller;


use LO\Application;
use LO\Model\Entity\Queue;

class Settings {
    public function getRequestTypeAction(Application $app){
        return $app->json([
            Queue::TYPE_FLYER             => "Listing Flyer",
            Queue::TYPE_PROPERTY_APPROVAL => "Property Approval",
        ]);
    }

    public function getRequestStateAction(Application $app){
        return $app->json([
            Queue::STATE_APPROVED    => "Approved",
            Queue::STATE_DECLINED    => "Declined",
            Queue::STATE_REQUESTED   => "Pending",
            Queue::STATE_LISTING_FLYER_PENDING => "Pending",
        ]);
    }
} 