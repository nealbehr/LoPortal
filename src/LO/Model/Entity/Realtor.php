<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 3/22/15
 * Time: 5:04 PM
 */

namespace LO\Model\Entity;


use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Column;
use LO\Validator\FullName;

/**
 * @Entity
 * @Table(name="realtor")
 */
class Realtor extends Base
{

    /**
     * @Column(type="string", length=255)
     */
    protected $bre_number;

    /**
     * @Column(type="string", length=255)
     * @Assert\NotBlank(message = "Phone should not be blank.", groups = {"main"})
     * @Assert\Regex(
     *               pattern = "/^[0-9+\(\)#\.\s\/ext-]+$/",
     *               message = "Please input a valid US phone number including 3 digit area code and 7 digit number.",
     *               groups = {"main"}
     * )
     */
    protected $phone;

    /**
     * @Column(type="string", length=255)
     * @Assert\Email();
     */
    protected $email;

    /**
     * @Column(type="string", length=65536)
     * @Assert\Length(
     *              max = 65536,
     *              maxMessage = "photo url cannot be longer than {{ limit }} characters"
     * )
     */
    protected $photo;

    /**
     * @Column(type="string", length=50)
     * @Assert\Length(
     *              max = 50,
     *              maxMessage = "Realty name cannot be longer than {{ limit }} characters"
     * )
     */
    protected $realty_name;

    /**
     * @Column(type="string", length=255)
     * @Assert\Length(
     *              max = 255,
     *              maxMessage = "Realty logo cannot be longer than {{ limit }} characters"
     * )
     */
    protected $realty_logo;

    /**
     * @Column(type="string", length=255)
     * @Assert\NotBlank(message = "First name should not be blank.", groups = {"main"})
     * @Assert\Regex(
     *               pattern = "/^([A-Za-z_\s]+)$/",
     *               message = "First name is invalid.",
     *               groups = {"main"}
     * )
     * @FullName(groups = {"main"})
     */
    protected $first_name;

    /**
     * @Column(type="string", length=255)
     * @Assert\NotBlank(message = "Last name should not be blank.", groups = {"main"})
     * @Assert\Regex(
     *               pattern = "/^([A-Za-z_\s]+)$/",
     *               message = "Last name is invalid.",
     *               groups = {"main"}
     * )
     * @FullName(groups = {"main"})
     */
    protected $last_name;

    public function setLastName($param)
    {
        $this->last_name = $param;

        return $this;
    }

    public function setFirstName($param)
    {
        $this->first_name = $param;
    }

    public function setBreNumber($param)
    {
        $this->bre_number = $param;

        return $this;
    }

    public function setPhone($param)
    {
        $this->phone = $param;

        return $this;
    }

    public function setEmail($param)
    {
        $this->email = $param;

        return $this;
    }

    public function setPhoto($param)
    {
        $this->photo = $param;

        return $this;
    }

    public function getLastName()
    {
        return $this->last_name;
    }

    public function getFirstName()
    {
        return $this->first_name;
    }

    public function getPhoto()
    {
        return $this->photo;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getPhone()
    {
        return $this->phone;
    }

    public function getBreNumber()
    {
        return $this->bre_number;
    }

    /**
     * @return mixed
     */
    public function getRealtyName()
    {
        return $this->realty_name;
    }

    /**
     * @param mixed $realty_name
     */
    public function setRealtyName($realty_name)
    {
        $this->realty_name = $realty_name;
    }

    /**
     * @return mixed
     */
    public function getRealtyLogo()
    {
        return $this->realty_logo;
    }

    /**
     * @param mixed $realty_logo
     */
    public function setRealtyLogo($realty_logo)
    {
        $this->realty_logo = $realty_logo;
    }

    public function getRealty()
    {
        $realtyCompany = new RealtyCompany();
        $realtyCompany->setName($this->realty_name);
        $realtyCompany->setLogo($this->realty_logo);
        return $realtyCompany;
    }

    public function setRealty(RealtyCompany $realty)
    {
        $this->realty_name = $realty->getName();
        $this->realty_logo = $realty->getLogo();
    }

    public function getPublicInfo()
    {
        $result = $this->toArray();
        unset($result['realty_logo'], $result['realty_name'], $result['created_at'], $result['updated_at']);
        $realtyCompany = new RealtyCompany();
        $realtyCompany->setName($this->realty_name);
        $realtyCompany->setLogo($this->realty_logo);
        $result['realty'] = $realtyCompany->toArray();

        return $result;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->first_name . " " . $this->last_name;
    }
}