<?php namespace LO\Model\Entity;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\UniqueConstraint;
use LO\Validator\FullName;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Entity
 * @Table(
 * name="realtor",
 * uniqueConstraints={@UniqueConstraint(name="first_last_name_unique",columns={"first_name", "last_name"})})
 */
class Realtor extends Base
{
    /**
     * @Column(type="string")
     */
    protected $deleted = '0';

    /**
     * @Column(type="integer")
     * @Assert\Type(type="numeric")
     */
    protected $realty_company_id;

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

    /**
     * @Column(type="string", length=255)
     */
    protected $bre_number;

    /**
     * @Column(type="string", length=100)
     * @Assert\NotBlank(message = "Phone should not be blank.", groups = {"main"})
     * @Assert\Regex(
     *               pattern = "/^[0-9+\(\)#\.\s\/ext-]+$/",
     *               message = "Please input a valid US phone number including 3 digit area code and 7 digit number.",
     *               groups = {"main"}
     * )
     */
    protected $phone;

    /**
     * @Column(type="string", length=50)
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

    public function getDeleted()
    {
        return $this->deleted;
    }

    public function setDeleted($param)
    {
        $this->deleted = $param;

        return $this;
    }

    public function getRealtyCompanyId()
    {
        return $this->realty_company_id;
    }

    public function setRealtyCompanyId($param)
    {
        $this->realty_company_id = $param;

        return $this;
    }

    public function getFirstName()
    {
        return $this->first_name;
    }

    public function setFirstName($param)
    {
        $this->first_name = $param;

        return $this;
    }

    public function getLastName()
    {
        return $this->last_name;
    }

    public function setLastName($param)
    {
        $this->last_name = $param;

        return $this;
    }

    public function getBreNumber()
    {
        return $this->bre_number;
    }

    public function setBreNumber($param)
    {
        $this->bre_number = $param;

        return $this;
    }

    public function getPhone()
    {
        return $this->phone;
    }

    public function setPhone($param)
    {
        $this->phone = $param;

        return $this;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($param)
    {
        $this->email = $param;

        return $this;
    }

    public function getPhoto()
    {
        return $this->photo;
    }

    public function setPhoto($param)
    {
        $this->photo = $param;

        return $this;
    }
}
