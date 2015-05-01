<?php

namespace DoctrineProxy\__CG__\LO\Model\Entity;

/**
 * DO NOT EDIT THIS FILE - IT WAS CREATED BY DOCTRINE'S PROXY GENERATOR
 */
class User extends \LO\Model\Entity\User implements \Doctrine\ORM\Proxy\Proxy
{
    /**
     * @var \Closure the callback responsible for loading properties in the proxy object. This callback is called with
     *      three parameters, being respectively the proxy object to be initialized, the method that triggered the
     *      initialization process and an array of ordered parameters that were passed to that method.
     *
     * @see \Doctrine\Common\Persistence\Proxy::__setInitializer
     */
    public $__initializer__;

    /**
     * @var \Closure the callback responsible of loading properties that need to be copied in the cloned object
     *
     * @see \Doctrine\Common\Persistence\Proxy::__setCloner
     */
    public $__cloner__;

    /**
     * @var boolean flag indicating if this object was already initialized
     *
     * @see \Doctrine\Common\Persistence\Proxy::__isInitialized
     */
    public $__isInitialized__ = false;

    /**
     * @var array properties to be lazy loaded, with keys being the property
     *            names and values being their default values
     *
     * @see \Doctrine\Common\Persistence\Proxy::__getLazyProperties
     */
    public static $lazyPropertiesDefaults = array();



    /**
     * @param \Closure $initializer
     * @param \Closure $cloner
     */
    public function __construct($initializer = null, $cloner = null)
    {

        $this->__initializer__ = $initializer;
        $this->__cloner__      = $cloner;
    }







    /**
     * 
     * @return array
     */
    public function __sleep()
    {
        if ($this->__isInitialized__) {
            return array('__isInitialized__', 'id', 'first_name', 'last_name', 'email', 'gender', 'password', 'picture', 'roles', 'state', 'lender', 'salt', 'title', 'account_name', 'street', 'city', 'province', 'zip_code', 'phone', 'mobile', 'nmls', 'pmp', 'territory', 'sales_director', 'created_at', 'updated_at');
        }

        return array('__isInitialized__', 'id', 'first_name', 'last_name', 'email', 'gender', 'password', 'picture', 'roles', 'state', 'lender', 'salt', 'title', 'account_name', 'street', 'city', 'province', 'zip_code', 'phone', 'mobile', 'nmls', 'pmp', 'territory', 'sales_director', 'created_at', 'updated_at');
    }

    /**
     * 
     */
    public function __wakeup()
    {
        if ( ! $this->__isInitialized__) {
            $this->__initializer__ = function (User $proxy) {
                $proxy->__setInitializer(null);
                $proxy->__setCloner(null);

                $existingProperties = get_object_vars($proxy);

                foreach ($proxy->__getLazyProperties() as $property => $defaultValue) {
                    if ( ! array_key_exists($property, $existingProperties)) {
                        $proxy->$property = $defaultValue;
                    }
                }
            };

        }
    }

    /**
     * 
     */
    public function __clone()
    {
        $this->__cloner__ && $this->__cloner__->__invoke($this, '__clone', array());
    }

    /**
     * Forces initialization of the proxy
     */
    public function __load()
    {
        $this->__initializer__ && $this->__initializer__->__invoke($this, '__load', array());
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __isInitialized()
    {
        return $this->__isInitialized__;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __setInitialized($initialized)
    {
        $this->__isInitialized__ = $initialized;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __setInitializer(\Closure $initializer = null)
    {
        $this->__initializer__ = $initializer;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __getInitializer()
    {
        return $this->__initializer__;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __setCloner(\Closure $cloner = null)
    {
        $this->__cloner__ = $cloner;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific cloning logic
     */
    public function __getCloner()
    {
        return $this->__cloner__;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     * @static
     */
    public function __getLazyProperties()
    {
        return self::$lazyPropertiesDefaults;
    }

    
    /**
     * {@inheritDoc}
     */
    public function setState($sate)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setState', array($sate));

        return parent::setState($sate);
    }

    /**
     * {@inheritDoc}
     */
    public function getLender()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getLender', array());

        return parent::getLender();
    }

    /**
     * {@inheritDoc}
     */
    public function setLender($param)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setLender', array($param));

        return parent::setLender($param);
    }

    /**
     * {@inheritDoc}
     */
    public function getRoles()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getRoles', array());

        return parent::getRoles();
    }

    /**
     * {@inheritDoc}
     */
    public function getPassword()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getPassword', array());

        return parent::getPassword();
    }

    /**
     * {@inheritDoc}
     */
    public function setPassword($password)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setPassword', array($password));

        return parent::setPassword($password);
    }

    /**
     * {@inheritDoc}
     */
    public function getEmail()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getEmail', array());

        return parent::getEmail();
    }

    /**
     * {@inheritDoc}
     */
    public function setEmail($email)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setEmail', array($email));

        return parent::setEmail($email);
    }

    /**
     * {@inheritDoc}
     */
    public function getFirstName()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getFirstName', array());

        return parent::getFirstName();
    }

    /**
     * {@inheritDoc}
     */
    public function setFirstName($first_name)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setFirstName', array($first_name));

        return parent::setFirstName($first_name);
    }

    /**
     * {@inheritDoc}
     */
    public function getGender()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getGender', array());

        return parent::getGender();
    }

    /**
     * {@inheritDoc}
     */
    public function setGender($gender)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setGender', array($gender));

        return parent::setGender($gender);
    }

    /**
     * {@inheritDoc}
     */
    public function getLastName()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getLastName', array());

        return parent::getLastName();
    }

    /**
     * {@inheritDoc}
     */
    public function setLastName($last_name)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setLastName', array($last_name));

        return parent::setLastName($last_name);
    }

    /**
     * {@inheritDoc}
     */
    public function getUsername()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getUsername', array());

        return parent::getUsername();
    }

    /**
     * {@inheritDoc}
     */
    public function getPicture()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getPicture', array());

        return parent::getPicture();
    }

    /**
     * {@inheritDoc}
     */
    public function setPicture($picture)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setPicture', array($picture));

        return parent::setPicture($picture);
    }

    /**
     * {@inheritDoc}
     */
    public function addRole($role)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'addRole', array($role));

        return parent::addRole($role);
    }

    /**
     * {@inheritDoc}
     */
    public function removeRole($removableRole)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'removeRole', array($removableRole));

        return parent::removeRole($removableRole);
    }

    /**
     * {@inheritDoc}
     */
    public function setRoles(array $roles)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setRoles', array($roles));

        return parent::setRoles($roles);
    }

    /**
     * {@inheritDoc}
     */
    public function getState()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getState', array());

        return parent::getState();
    }

    /**
     * {@inheritDoc}
     */
    public function __toString()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, '__toString', array());

        return parent::__toString();
    }

    /**
     * {@inheritDoc}
     */
    public function setSalt($salt)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setSalt', array($salt));

        return parent::setSalt($salt);
    }

    /**
     * {@inheritDoc}
     */
    public function generateSalt()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'generateSalt', array());

        return parent::generateSalt();
    }

    /**
     * {@inheritDoc}
     */
    public function generatePassword()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'generatePassword', array());

        return parent::generatePassword();
    }

    /**
     * {@inheritDoc}
     */
    public function getSalt()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getSalt', array());

        return parent::getSalt();
    }

    /**
     * {@inheritDoc}
     */
    public function eraseCredentials()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'eraseCredentials', array());

        return parent::eraseCredentials();
    }

    /**
     * {@inheritDoc}
     */
    public function setTitle($param)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setTitle', array($param));

        return parent::setTitle($param);
    }

    /**
     * {@inheritDoc}
     */
    public function setAccountName($param)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setAccountName', array($param));

        return parent::setAccountName($param);
    }

    /**
     * {@inheritDoc}
     */
    public function setStreet($param)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setStreet', array($param));

        return parent::setStreet($param);
    }

    /**
     * {@inheritDoc}
     */
    public function setCity($param)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setCity', array($param));

        return parent::setCity($param);
    }

    /**
     * {@inheritDoc}
     */
    public function setProvince($param)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setProvince', array($param));

        return parent::setProvince($param);
    }

    /**
     * {@inheritDoc}
     */
    public function setZipCode($param)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setZipCode', array($param));

        return parent::setZipCode($param);
    }

    /**
     * {@inheritDoc}
     */
    public function setPhone($param)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setPhone', array($param));

        return parent::setPhone($param);
    }

    /**
     * {@inheritDoc}
     */
    public function setMobile($param)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setMobile', array($param));

        return parent::setMobile($param);
    }

    /**
     * {@inheritDoc}
     */
    public function setNmls($param)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setNmls', array($param));

        return parent::setNmls($param);
    }

    /**
     * {@inheritDoc}
     */
    public function setPmp($param)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setPmp', array($param));

        return parent::setPmp($param);
    }

    /**
     * {@inheritDoc}
     */
    public function setTerritory($param)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setTerritory', array($param));

        return parent::setTerritory($param);
    }

    /**
     * {@inheritDoc}
     */
    public function setSalesDirector($param)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setSalesDirector', array($param));

        return parent::setSalesDirector($param);
    }

    /**
     * {@inheritDoc}
     */
    public function getPublicInfo()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getPublicInfo', array());

        return parent::getPublicInfo();
    }

    /**
     * {@inheritDoc}
     */
    public function getTitle()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getTitle', array());

        return parent::getTitle();
    }

    /**
     * {@inheritDoc}
     */
    public function getSalesDirector()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getSalesDirector', array());

        return parent::getSalesDirector();
    }

    /**
     * {@inheritDoc}
     */
    public function getPhone()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getPhone', array());

        return parent::getPhone();
    }

    /**
     * {@inheritDoc}
     */
    public function getMobile()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getMobile', array());

        return parent::getMobile();
    }

    /**
     * {@inheritDoc}
     */
    public function getNmls()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getNmls', array());

        return parent::getNmls();
    }

    /**
     * {@inheritDoc}
     */
    public function toArray()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'toArray', array());

        return parent::toArray();
    }

    /**
     * {@inheritDoc}
     */
    public function fillFromArray(array $param)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'fillFromArray', array($param));

        return parent::fillFromArray($param);
    }

    /**
     * {@inheritDoc}
     */
    public function getId()
    {
        if ($this->__isInitialized__ === false) {
            return  parent::getId();
        }


        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getId', array());

        return parent::getId();
    }

}