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
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\JoinColumn;

/**
 * @Entity
 * @Table(name="request_approval")
 */
class RequestApproval extends Base{
    /**
     * @Column(type="integer")
     */
    protected $queue_id;

    /**
     * @OneToOne(targetEntity="Queue", fetch="LAZY")
     * @JoinColumn(name="queue_id", referencedColumnName="id")
     **/
    protected $queue;

    public function setQueueId($param){
        $this->queue_id = $param;

        return $this;
    }

    public function setQueue(Queue $queue){
        $this->queue = $queue;

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