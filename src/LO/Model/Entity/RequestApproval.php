<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 4/8/15
 * Time: 4:16 PM
 */

namespace LO\Model\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Column;

/**
 * @Entity
 * @Table(name="request_approval")
 */
class RequestApproval extends Base{
    /**
     * @Column(type="integer")
     * @Assert\NotBlank(message = "Queue id should not be blank.")
     * @Assert\Type(type="numeric")
     */
    protected $queue_id;

    public function setQueueId($param){
        $this->queue_id = $param;

        return $this;
    }

    public static function getAllowedStates(){
        return [
            Queue::STATE_REQUESTED,
            Queue::STATE_APPROVED,
            Queue::STATE_DECLINED,
        ];
    }
}