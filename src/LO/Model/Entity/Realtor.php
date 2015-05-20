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
class Realtor extends Base {

    const CLASS_NAME = 'LO\Model\Entity\Realtor';

    /**
     * @Column(type="string", length=255)
     */
    protected $bre_number;

    /**
     * @Column(type="string", length=255)
     * @Assert\NotBlank(message = "Phone should not be blank.", groups = {"main"})
     * @Assert\Regex(
     *               pattern = "/^\(?(\d{3})\)?[-\. ]?(\d{3})[-\. ]?(\d{4})$/",
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
     * @Assert\NotBlank(message = "Photo should not be blank.", groups = {"main"})
     * @Assert\Length(
     *              max = 65536,
     *              maxMessage = "photo url cannot be longer than {{ limit }} characters"
     * )
     */
    protected $photo;

    /**
     * @Column(type="string", length=255)
     * @Assert\Length(
     *              max = 255,
     *              maxMessage = "agency cannot be longer than {{ limit }} characters"
     * )
     */
    protected $estate_agency;

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

    public function setLastName($param){
        $this->last_name = $param;

        return $this;
    }

    public function setEstateAgency($param){
        $this->estate_agency = $param;

        return $this;
    }

    public function setFirstName($param){
        $this->first_name = $param;
    }

    public function setBreNumber($param){
        $this->bre_number = $param;

        return $this;
    }
    public function setPhone($param){
        $this->phone = $param;

        return $this;
    }
    public function setEmail($param){
        $this->email = $param;

        return $this;
    }
    public function setPhoto($param){
        $this->photo = $param;

        return $this;
    }

    public function setAgency($param){
        $this->estate_agency = $param;

        return $this;
    }

    public function getLastName(){
        return $this->last_name;
    }

    public function getFirstName(){
        return $this->first_name;
    }

    public function getEstateAgency(){
        return $this->estate_agency;
    }

    public function getPhoto(){
        return $this->photo;
    }

    public function getEmail(){
        return $this->email;
    }

    public function getPhone(){
        return $this->phone;
    }

    public function getBreNumber(){
        return $this->bre_number;
    }


}