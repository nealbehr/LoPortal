<?php
/**
 * Created by IntelliJ IDEA.
 * User: mac
 * Date: 5/28/15
 * Time: 15:35
 */

namespace LO\Model\Entity;

use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Column;

/**
 * @Entity
 * @Table(name="address")
 */
class Address extends Base {

    /**
     * @Column(type="string", length=100)
     */
    protected $place_id;

    /**
     * @Column(type="string", length=255)
     */
    protected $base_original_address;

    /**
     * @Column(type="string", length=255)
     */
    protected $formatted_address;

    /**
     * @Column(type="string", length=64)
     */
    protected $street_number;

    /**
     * @Column(type="string", length=50)
     */
    protected $apartment;

    /**
     * @Column(type="string", length=64)
     */
    protected $street;

    /**
     * @Column(type="string", length=30)
     */
    protected $city;

    /**
     * @Column(type="string", length=2)
     */
    protected $state;

    /**
     * @Column(type="string", length=10)
     */
    protected $postal_code;

    /**
     * @return mixed
     */
    public function getPlaceId()
    {
        return $this->place_id;
    }

    /**
     * @param mixed $place_id
     */
    public function setPlaceId($place_id)
    {
        $this->place_id = $place_id;
    }

    public function getBaseOriginalAddress()
    {
        return $this->base_original_address;
    }

    public function setBaseOriginalAddress($param)
    {
        $this->base_original_address = $param;
        return $this;
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

    /**
     * @return mixed
     */
    public function getFormattedAddress()
    {
        return $this->formatted_address;
    }

    /**
     * @param mixed $formatted_address
     */
    public function setFormattedAddress($formatted_address)
    {
        $this->formatted_address = $formatted_address;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param mixed $city
     */
    public function setCity($city)
    {
        $this->city = $city;
    }

    /**
     * @return mixed
     */
    public function getPostalCode()
    {
        return $this->postal_code;
    }

    /**
     * @param mixed $postal_code
     */
    public function setPostalCode($postal_code)
    {
        $this->postal_code = $postal_code;
    }

    /**
     * @return mixed
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * @param mixed $street
     */
    public function setStreet($street)
    {
        $this->street = $street;
    }

    /**
     * @return mixed
     */
    public function getStreetNumber()
    {
        return $this->street_number;
    }

    /**
     * @param mixed $street_number
     */
    public function setStreetNumber($street_number)
    {
        $this->street_number = $street_number;
    }

    public function getApartment()
    {
        return $this->apartment;
    }

    public function setApartment($param)
    {
        $this->apartment = $param;

        return $this;
    }

    public function toArray() {
        return array(
            'id' => $this->id,
            'formatted_address' => $this->formatted_address,
            'state' => $this->state,
            'сity' => $this->city,
            'postal_code' => $this->postal_code,
            'street' => $this->street,
            'place_id' => $this->place_id,
            'street_number' => $this->street_number,
            'apartment'     => $this->apartment
        );
    }
} 