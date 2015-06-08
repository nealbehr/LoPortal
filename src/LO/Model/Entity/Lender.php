<?php
/**
 * Created by IntelliJ IDEA.
 * User: Dmitry K.
 * Date: 5/15/15
 * Time: 12:55
 */

namespace LO\Model\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\OneToMany;

/**
 * @Entity
 * @Table(name="lender")
 */
class Lender extends Base {

    public function __construct() {
        $this->disclosures = new ArrayCollection();
    }

    /**
     * @Column(type="string", length=50)
     * @Assert\NotBlank(message="Lender name should not be blank.", groups = {"main"})
     * @Assert\Length(
     *              max = 50,
     *              maxMessage = "Lender name cannot be longer than {{ limit }} characters" )
     *
     */
    protected $name;

    /**
     * @Column(type="string", length=255)
     */
    protected $picture;

    /**
     * @OneToMany(targetEntity="LenderDisclosure", mappedBy="lender", fetch="LAZY", cascade={"persist", "remove"})
     */
    protected $disclosures;

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getPicture()
    {
        return $this->picture;
    }

    /**
     * @return mixed
     */
    public function getDisclosures()
    {
        return $this->disclosures;
    }

    /**
     * @param mixed $disclosures
     */
    public function setDisclosures($disclosures)
    {
        $this->disclosures = $disclosures;
    }

    public function getDisclosureForState($state) {
        $disclosureObjects = $this->getDisclosures();
        $default = '';
        if($disclosureObjects != null) {
            foreach($disclosureObjects as $disclosureObject) {
                /** @var LenderDisclosure $disclosureObject */
                if($disclosureObject->getState() == $state) {
                    return $disclosureObject->getDisclosure();
                } else if ($disclosureObject->getState() == LenderDisclosure::ALL_STATES) {
                    $default = $disclosureObject->getDisclosure();
                }
            }
        }
        return $default;
    }

    /**
     * @param mixed $picture
     */
    public function setPicture($picture)
    {
        $this->picture = $picture;
    }

    public function toArray() {
        return array(
            'id' => $this->id,
            'name' => $this->name,
        );
    }

    public function toFullArray() {

        $disclosuresArray = array();
        $disclosureObjects = $this->getDisclosures();
        if($disclosureObjects != null) {
            foreach($disclosureObjects as $disclosureObject) {
                /** @var LenderDisclosure $disclosureObject */
                $disclosuresArray[] = $disclosureObject->toArray();
            }
        }

        return array(
            'id' => $this->id,
            'name' => $this->name,
            'picture' => $this->picture,
            'disclosures' => $disclosuresArray
        );

    }
}