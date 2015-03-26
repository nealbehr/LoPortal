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
class Realtor extends Base{
    /**
     * @Column(type="string", length=255)
     * @Assert\NotBlank()
     */
    protected $bre_number;

    /**
     * @Column(type="string", length=255)
     * @Assert\NotBlank()
     */
    protected $phone;

    /**
     * @Column(type="string", length=255)
     * @Assert\Email();
     */
    protected $email;

    /**
     * @Column(type="string", length=65536)
     * @Assert\NotBlank()
     * @Assert\Length(
     *              max = 65536,
     *              maxMessage = "photo url cannot be longer than {{ limit }} characters"
     * )
     */
    protected $photo;

    /**
     * @Column(type="string", length=255)
     * @Assert\NotBlank()
     * @Assert\Length(
     *              max = 255,
     *              maxMessage = "agency cannot be longer than {{ limit }} characters"
     * )
     */
    protected $estate_agency;

    /**
     * @Column(type="string", length=255)
     * @Assert\NotBlank()
     * @FullName()
     */
    protected $first_name;

    /**
     * @Column(type="string", length=255)
     * @Assert\NotBlank()
     * @FullName()
     */
    protected $last_name;

    public function setBre($param){
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


}