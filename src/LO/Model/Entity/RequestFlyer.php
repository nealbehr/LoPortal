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
 * @Table(name="request_flyer")
 */
class RequestFlyer extends Base {

    const CLASS_NAME = 'LO\Model\Entity\RequestFlyer';

    /**
     * @Column(type="integer")
     */
    protected $queue_id;

    /**
     * @Column(type="string")
     * @Assert\NotBlank(message = "Listing price should not be blank.", groups = {"main"})
     */
    protected $listing_price;

    /**
     * @Column(type="string")
     * @Assert\NotBlank(message = "Photo id should not be blank.", groups = {"main"})
     * @Assert\Length(
     *              max = 65536,
     *              maxMessage = "Photo url cannot be longer than {{ limit }} characters"
     * )
     */
    protected $photo;

    /**
     * @OneToOne(targetEntity="Queue", fetch="LAZY")
     * @JoinColumn(name="queue_id", referencedColumnName="id")
     **/
    private $queue;

    /**
     * @OneToOne(targetEntity="Realtor", fetch="LAZY")
     * @JoinColumn(name="realtor_id", referencedColumnName="id")
     **/
    private $realtor;

    public function setQueueId($param){
        $this->queue_id = $param;

        return $this;
    }

    public function setPhoto($param){
        $this->photo = $param;

        return $this;
    }

    public function getPhoto(){
        return $this->photo;
    }

    public function getQueueId(){
        return $this->queue_id;
    }

    public static function getAllowedStates(){
        return [
            Queue::STATE_REQUESTED,
            Queue::STATE_APPROVED,
            Queue::STATE_DECLINED,
            Queue::STATE_LISTING_FLYER_PENDING,
        ];
    }

    /**
     * @return Queue
     */
    public function getQueue(){
        return $this->queue;
    }

    public function setQueue(Queue $queue){
        $this->queue = $queue;

        return $this;
    }

    /**
     * @return Realtor
     */
    public function getRealtor()
    {
        return $this->realtor;
    }

    /**
     * @param Realtor $realtor
     */
    public function setRealtor($realtor)
    {
        $this->realtor = $realtor;
    }

    public function getListingPrice(){
        return $this->listing_price;
    }

    public function setListingPrice($param){
        $this->listing_price = $param;

        return $this;
    }

}