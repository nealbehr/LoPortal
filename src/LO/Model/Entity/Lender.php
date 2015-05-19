<?php
/**
 * Created by IntelliJ IDEA.
 * User: Dmitry K.
 * Date: 5/15/15
 * Time: 12:55
 */

namespace LO\Model\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Column;

/**
 * @Entity
 * @Table(name="lender")
 */
class Lender extends Base {

    const CLASS_NAME = 'LO\Model\Entity\Lender';

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
     * @Assert\NotBlank(message="Address should not be blank.", groups = {"main"})
     * @Assert\Length(
     *              max = 255,
     *              maxMessage = "Address cannot be longer than {{ limit }} characters" )
     */
    protected $address;

    /**
     * @Column(type="string", length=65536)
     * @Assert\NotBlank(message="Disclosure should not be blank.", groups = {"main"})
     * @Assert\Length(
     *              max = 65536,
     *              maxMessage = "Disclosure cannot be longer than {{ limit }} characters" )
     */
    protected $disclosure;

    /**
     * @Column(type="string", length=255)
     */
    protected $picture;

    /**
     * @return mixed
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param mixed $address
     */
    public function setAddress($address)
    {
        $this->address = $address;
    }

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
        return array(
            'id' => $this->id,
            'name' => $this->name,
            'address' => $this->address,
            'disclosure' => $this->disclosure,
            'picture' => $this->picture
        );

    }
}