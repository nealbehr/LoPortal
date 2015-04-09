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
use Symfony\Component\Validator\ExecutionContextInterface;

/**
 * @Entity
 * @Table(name="queue")
 */
class Queue extends Base{
    const STATE_LISTING_FLYER_PENDING = 1;
    const STATE_REQUESTED   = 2;
    const STATE_APPROVED    = 3;
    const STATE_DECLINED    = 4;

    const TYPE_FLYER             = 1;
    const TYPE_PROPERTY_APPROVAL = 2;

    /**
     * @Column(type="integer")
     * @Assert\NotBlank(message="Firstrex id should not be blank.")
     * @Assert\Type(type="numeric")
     */
    protected $firstrex_id;

    /**
     * @Column(type="smallint")
     * @Assert\NotBlank(message="Request type should not be blank.")
     */
    protected $request_type;

    /**
     * @Column(type="string", length=255)
     * @Assert\Length(
     *              max = 65536,
     *              maxMessage = "photo url cannot be longer than {{ limit }} characters"
     * )
     */
    protected $mls_number;

    /**
     * @Column(type="smallint")
     * @Assert\NotBlank(message="State should not be blank.")
     * @Assert\Type(type="numeric")
     */
    protected $state;

    /**
     * @Column(type="string", length=255)
     * @Assert\NotBlank(message="Address should not be blank.")
     * @Assert\Length(
     *              max = 65536,
     *              maxMessage = "photo url cannot be longer than {{ limit }} characters"
     * )
     */
    protected $address;

    /**
     * @Column(type="integer")
     * @Assert\NotBlank(message="User id should not be blank.")
     * @Assert\Type(type="numeric")
     */
    protected $user_id;

    public function __construct(){
        parent::__construct();

        $this->state = self::STATE_REQUESTED;
    }

    public function getAddress(){
        return $this->address;
    }

    public function getMlsNumber(){
        return $this->mls_number;
    }

    public function set1RexId($param){
        $this->firstrex_id = $param;

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

    public function setMlsNumber($param){
        $this->mls_number = $param;

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

    /**
     * @Assert\Callback
     */
    public function isStateValid(ExecutionContextInterface $context){
        $allowedStates = static::TYPE_FLYER == $this->request_type
            ? RequestFlyer::getAllowedStates()
            : RequestApproval::getAllowedStates();
/** @todo поиграться повесь колбек на само поле state */
        if (!in_array($this->getState(), $allowedStates)) {
            $context->addViolationAt(
                'state',
                sprintf('Field "State" have not contained allowed states. Allowed states for type \'%d\' are [%s]', $this->request_type, implode(', ', $allowedStates)),
                array(),
                null
            );
        }
    }

    static public function getTypes(){
        return [
            static::TYPE_PROPERTY_APPROVAL,
            static::TYPE_FLYER,
        ];
    }

    static public function getStates(){
        return [
            static::STATE_REQUESTED,
            static::STATE_APPROVED,
            static::STATE_DECLINED,
            static::STATE_LISTING_FLYER_PENDING,
        ];
    }
} 