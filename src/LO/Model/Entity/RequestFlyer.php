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
class RequestFlyer extends Base{
    /**
     * @Column(type="integer")
     */
    protected $queue_id;

    /**
     * @Column(type="integer")
     * @Assert\NotBlank(message = "Realtor id should not be blank.")
     * @Assert\Type(type="numeric")
     */
    protected $realtor_id;

    /**
     * @Column(type="string")
     * @Assert\Length(
     *              max = 65536,
     *              maxMessage = "photo url cannot be longer than {{ limit }} characters"
     * )
     */
    protected $pdf_link;


    /**
     * @Column(type="float")
     * @Assert\NotBlank(message = "Listing price should not be blank.")
     * @Assert\Type(type="numeric")
     */
    protected $listing_price;

    /**
     * @Column(type="string")
     * @Assert\NotBlank(message = "Photo id should not be blank.")
     * @Assert\Length(
     *              max = 65536,
     *              maxMessage = "Photo url cannot be longer than {{ limit }} characters"
     * )
     */
    protected $photo;

    public function setQueueId($param){
        $this->queue_id = $param;

        return $this;
    }

    public function setRealtorId($param){
        $this->realtor_id = $param;

        return $this;
    }

    public function setPhoto($param){
        $this->photo = $param;

        return $this;
    }

    public function getPhoto(){
        return $this->photo;
    }

    public function getPdfLink(){
        return $this->pdf_link;
    }

    public function setPdfLink($param){
        $this->pdf_link = $param;

        return $this;
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
     * @OneToOne(targetEntity="Queue", fetch="LAZY")
     * @JoinColumn(name="queue_id", referencedColumnName="id")
     **/
    protected $queue;

    public function getQueue(){
        return $this->queue;
    }

    public function setQueue(Queue $queue){
        $this->queue = $queue;

        return $this;
    }

    public function getListingPrice(){
        return $this->listing_price;
    }

    public function setListingPrice($param){
        $this->listing_price = $param;

        return $this;
    }

}