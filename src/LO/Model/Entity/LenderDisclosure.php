<?php
/**
 * Created by IntelliJ IDEA.
 * User: mac
 * Date: 6/1/15
 * Time: 15:00
 */

namespace LO\Model\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;

/**
 * @Entity
 * @Table(name="lender_disclosure")
 */
class LenderDisclosure extends Base {

    const ALL_STATES = 'US';

    /**
     * @ManyToOne(targetEntity="Lender", inversedBy="disclosures")
     * @JoinColumn(name="lender_id", referencedColumnName="id")
     */
    protected $lender;

    /**
     * @Column(type="string", length=65536)
     * @Assert\NotBlank(message="Disclosure should not be blank.", groups = {"main"})
     * @Assert\Length(
     *              max = 65536,
     *              maxMessage = "Disclosure cannot be longer than {{ limit }} characters" )
     */
    protected $disclosure;

    /**
     * @Column(type="string", length=2)
     */
    protected $state;

    /**
     * @return mixed
     */
    public function getDisclosure()
    {
        return $this->disclosure;
    }

    /**
     * @param mixed $disclosure
     */
    public function setDisclosure($disclosure)
    {
        $this->disclosure = $disclosure;
    }

    /**
     * @return mixed
     */
    public function getLender()
    {
        return $this->lender;
    }

    /**
     * @param mixed $lender
     */
    public function setLender($lender)
    {
        $this->lender = $lender;
    }

    /**
     * @return mixed
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param mixed $state
     */
    public function setState($state)
    {
        $this->state = $state;
    }

    public function toArray() {
        return array(
            'id' => $this->id,
            'state' => $this->state,
            'disclosure' => $this->disclosure
        );
    }
} 