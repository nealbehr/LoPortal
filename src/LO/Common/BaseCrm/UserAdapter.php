<?php
/**
 * User: Eugene Lysenko
 * Date: 1/4/16
 * Time: 11:56
 */
namespace LO\Common\BaseCrm;

use LO\Model\Entity\User;

class UserAdapter
{
    private $user;
    private $lender;
    private $address;

    public function __construct(User $model)
    {
        $this->user    = $model;
        $this->lender  = $model->getLender();
        $this->address = $model->getAddress();
    }

    public function getId()
    {
        return $this->user->getBaseId();
    }

    public function getEmail()
    {
        return $this->user->getEmail();
    }

    public function getNmls()
    {
        return $this->user->getNmls();
    }

    public function getPhone()
    {
        return $this->user->getPhone();
    }

    public function getMobile()
    {
        return $this->user->getMobile();
    }

    public function getFirstName()
    {
        return $this->user->getFirstName();
    }

    public function getLastName()
    {
        return $this->user->getLastName();
    }

    public function getTitle()
    {
        return $this->user->getTitle();
    }

    public function getSignedPMP()
    {
        return $this->user->getFirstTime();
    }

    public function getSubCompanyName()
    {
        return $this->lender->getName();
    }

    public function getPassword()
    {
        return $this->user->getPassword();
    }

    public function getCity()
    {
        return $this->address->getCity();
    }

    public function getStreet()
    {
        return $this->address->getStreetNumber().' '.$this->address->getStreet();
    }

    public function getPostalCode()
    {
        return $this->address->getPostalCode();
    }

    public function getState()
    {
        return $this->address->getState();
    }

    public function toArray()
    {
        return [
            'id'         => $this->getId(),
            'first_name' => $this->getFirstName(),
            'last_name'  => $this->getLastName(),
            'title'      => $this->getTitle(),
            'email'      => $this->getEmail(),
            'phone'      => $this->getPhone(),
            'mobile'     => $this->getMobile(),
            'address'    => [
                'city'        => $this->getCity(),
                'line1'       => $this->getStreet(),
                'postal_code' => $this->getPostalCode(),
                'state'       => $this->getState()
            ],
            'custom_fields' => [
                'NMLS'                   => $this->getNmls(),
                'Sub-Company Name (DBA)' => $this->getSubCompanyName(),
                'ESC Password'           => $this->getPassword(),
                'Signed PMP'             => $this->getSignedPMP()
            ]
        ];
    }
}
