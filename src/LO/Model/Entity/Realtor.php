<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 3/22/15
 * Time: 5:04 PM
 */

namespace LO\Model\Entity;

/**
 * @Entity
 * @Table(name="realtor")
 */
class Realtor extends Base{
    /**
     * @Column(type="string", length=255)
     */
    protected $bre_number;

    /**
     * @Column(type="string", length=255)
     */
    protected $phone;

    /**
     * @Column(type="string", length=255)
     */
    protected $email;

    /**
     * @Column(type="string", length=65536)
     */
    protected $photo;

    /**
     * @Column(type="string", length=255)
     */
    protected $estate_agency;

    /**
     * @Column(type="string", length=255)
     */
    protected $full_name;

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
    public function set($param){
        $this->phone = $param;

        return $this;
    }

    public function setAgency($param){
        $this->estate_agency = $param;

        return $this;
    }
}