<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 3/25/15
 * Time: 3:17 PM
 */

namespace LO\Model\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Column;

/**
 * @Entity
 * @Table(name="queue")
 */
class Queue extends Base{
    const STATE_IN_PROGRESS = 1;
    const STATE_REQUESTED   = 2;
    const STATE_APPROVED    = 3;
    const STATE_CANCELED    = 4;

    const TYPE_FLYER             = 1;
    const TYPE_PROPERTY_APPROVAL = 2;

    /**
     * @Column(type="integer")
     * @Assert\NotBlank()
     * @Assert\Type(type="numeric")
     */
    protected $firstrex_id;

    /**
     * @Column(type="smallint")
     * @Assert\NotBlank()
     */
    protected $request_type;

    /**
     * @Column(type="integer")
     * @Assert\NotBlank(groups={"flyer"})
     * @Assert\Type(type="numeric")
     */
    protected $realtor_id;

    /**
     * @Column(type="string", length=255)
     * @Assert\Length(
     *              max = 65536,
     *              maxMessage = "photo url cannot be longer than {{ limit }} characters"
     * )
     */
    protected $mls_number;

    /**
     * @Column(type="string")
     * @Assert\Length(
     *              max = 65536,
     *              maxMessage = "photo url cannot be longer than {{ limit }} characters"
     * )
     */
    protected $pdf_link;

    /**
     * @Column(type="smallint")
     * @Assert\NotBlank()
     * @Assert\Type(type="numeric")
     */
    protected $state;

    /**
     * @Column(type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\Length(
     *              max = 65536,
     *              maxMessage = "photo url cannot be longer than {{ limit }} characters"
     * )
     */
    protected $address;

    /**
     * @Column(type="string")
     * @Assert\NotBlank(groups={"flyer"})
     * @Assert\Length(
     *              max = 65536,
     *              maxMessage = "photo url cannot be longer than {{ limit }} characters"
     * )
     */
    protected $photo;

    /**
     * @Column(type="integer")
     * @Assert\NotBlank()
     * @Assert\Type(type="numeric")
     */
    protected $user_id;

    public function __construct(){
        parent::__construct();

        $this->state = self::STATE_REQUESTED;
    }

    public function set1RexId($param){
        $this->firstrex_id = $param;

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

    public function setType($param){
        $this->request_type = $param;

        return $this;
    }

    public function setUserId($param){
        $this->user_id = $param;

        return $this;
    }

    public function getState(){
        return $this->state;
    }

    public function setState($param){
        $this->state = $param;

        return $this;
    }

    public function setAddress($param){
        $this->address = $param;

        return $this;
    }
} 