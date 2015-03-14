<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 3/13/15
 * Time: 2:05 PM
 */

namespace LO\Model\Entity;

/**
 * @Entity
 * @Table(name="users",
 *      uniqueConstraints={@UniqueConstraint(name="email_unique",columns={"email"})})
 */
class User {
    /**
     * Roles list
     */
    const ROLE_USER  = "ROLE_USER";
    const ROLE_ADMIN = "ROLE_ADMIN";

    const STATE_ACTIVE  = 1;
    const STATE_BANNED  = 2;
    const STATE_DELETED = 3;

    /**
     * @Id
     * @Column(type="bigint")
     * @GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @Column(type="string")
     */
    protected $first_name;

    /**
     * @Column(type="string")
     */
    protected $last_name;

    /**
     * @Column(type="string")
     */
    protected $email;

    /**
     * @Column(type="string", nullable=true)
     */
    protected $gender;

    /**
     * @Column(type="string", nullable=false)
     */
    protected $password;

    /**
     * @Column(type="string", length=20)
     */
    protected $picture;

    /**
     * @Column(type="array")
     */
    protected $roles;

    /**
     * @Column(type="smallint")
     */
    protected $state;

    /**
     * @Column(type="datetime")
     */
    protected $created_at;

    /**
     * @Column(type="datetime")
     */
    protected $updated_at;

    /**
     * Init entity
     */
    public function __construct() {

//        $this->salt           = md5(time().rand(1,1000));
        $this->state          = self::STATE_ACTIVE;
    }

    /**
     * @return array
     */
    public static function getStates(){
        return [
            self::STATE_ACTIVE,
            self::STATE_BANNED,
            self::STATE_DELETED,
        ];
    }

    /**
     * @return array
     */
    public static function getAllowedRoles(){
        return [
            static::ROLE_ADMIN,
            static::ROLE_COACH,
            static::ROLE_USER,
        ];
    }

    public function setState($sate){
        $this->state = $sate;

        return $this;
    }

    /**
     * Returns the roles granted to the user.
     *
     * <code>
     * public function getRoles()
     * {
     *     return array('ROLE_USER');
     * }
     * </code>
     *
     * Alternatively, the roles might be stored on a ``roles`` property,
     * and populated in any number of different ways when the user object
     * is created.
     *
     * @return Role[] The user roles
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * Returns the password used to authenticate the user.
     *
     * This should be the encoded password. On authentication, a plain-text
     * password will be salted, encoded, and then compared to this value.
     *
     * @return string The password
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param mixed $password
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param String $email
     * @return $this
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }


    /**
     * @return mixed
     */
    public function getFirstName()
    {
        return $this->first_name;
    }

    /**
     * @param mixed $first_name
     */
    public function setFirstName($first_name)
    {
        $this->first_name = $first_name;
    }

    /**
     * @return mixed
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * @param mixed $gender
     */
    public function setGender($gender)
    {
        $this->gender = $gender;
    }

    /**
     * @return mixed
     */
    public function getLastName()
    {
        return $this->last_name;
    }

    /**
     * @param mixed $last_name
     */
    public function setLastName($last_name)
    {
        $this->last_name = $last_name;
    }


    /**
     * Returns the username used to authenticate the user.
     *
     * @return string The username
     */
    public function getUsername()
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getPicture() {
        return $this->picture;
    }

    /**
     * @param string $picture
     */
    public function setPicture($picture) {
        $this->picture = $picture;
    }


    /**
     * @param string $role
     */
    public function addRole($role) {
        $this->roles[] = $role;

        return $this;
    }

    public function getState(){
        return $this->state;
    }

    /**
     * @return string
     */
    public function __toString() {
        return $this->first_name . " " . $this->last_name;
    }
} 