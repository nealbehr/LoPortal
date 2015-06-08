<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 3/13/15
 * Time: 2:05 PM
 */

namespace LO\Model\Entity;

use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\UniqueConstraint;

/**
 * @Entity
 * @Table(name="users",
 *      uniqueConstraints={@UniqueConstraint(name="email_unique",columns={"email"})})
 */
class User extends Base implements UserInterface{

    /**
     * Roles list
     */
    const ROLE_USER  = "ROLE_USER";
    const ROLE_ADMIN = "ROLE_ADMIN";

    const STATE_ACTIVE  = 1;
    const STATE_BANNED  = 2;

    /**
     * @Id
     * @Column(type="bigint")
     * @GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @Column(type="string")
     */
    protected $deleted = '0';

    /**
     * @ManyToOne(targetEntity="Address", inversedBy="user", cascade={"persist", "remove", "merge"})
     **/
    protected $address;

    /**
     * @Column(type="string")
     *
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
     * @ManyToOne(targetEntity="Lender")
     * @JoinColumn(name="lender_id", referencedColumnName="id")
     **/
    protected $lender;

    /**
     * @Column(type="string", length=32)
     */
    protected $salt;

    /**
     * @Column(type="string", length=100)
     */
    protected $title;

    /**
     * @Column(type="string", length=100)
     */
    protected $phone;
    /**
     * @Column(type="string", length=100)
     */
    protected $mobile;
    /**
     * @Column(type="integer")
     */
    protected $nmls;

    /**
     * @Column(type="string", length=255)
     */
    protected $sales_director;

    /**
     * @Column(type="string", length=100)
     */
    protected $sales_director_email;

    /**
     * @Column(type="string", length=100)
     */
    protected $sales_director_phone;

    /**
     * Init entity
     */
    public function __construct() {
        parent::__construct();
        $this->salt   = $this->generateSalt();
        $this->state  = self::STATE_ACTIVE;
    }

    public function getDeleted() {
        return $this->deleted;
    }

    public function setDeleted($param) {
        $this->deleted = $param;

        return $this;
    }

    /**
     * @return array
     */
    public static function getStates(){
        return [
            self::STATE_ACTIVE,
            self::STATE_BANNED,
        ];
    }

    /**
     * @return array
     */
    public static function getAllowedRoles(){
        return [
            'user'  => static::ROLE_USER,
            'admin' => static::ROLE_ADMIN,
        ];
    }

    public function setState($sate){
        $this->state = $sate;

        return $this;
    }

    /**
     * @return Lender
     */
    public function getLender(){
        return $this->lender;
    }

    public function setLender($param){
        $this->lender = $param;

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
     * @param $password
     * @return $this
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
     * @param $first_name
     * @return $this
     */
    public function setFirstName($first_name)
    {
        $this->first_name = $first_name;

        return $this;
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
     * @param $last_name
     * @return $this
     */
    public function setLastName($last_name)
    {
        $this->last_name = $last_name;

        return $this;
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
     * @param $role
     * @return $this
     */
    public function addRole($role) {
        $this->roles[] = $role;

        return $this;
    }

    public function removeRole($removableRole){
        foreach($this->roles as $k => $role){
            if($role == $removableRole){
                unset($this->roles[$k]);
            }
        }

        return $this;
    }

    public function setRoles(array $roles){
        $this->roles = $roles;

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

    public function setSalt($salt){
        $this->salt = $salt;

        return $this;
    }

    public function generateSalt(){
        return md5(time() + mt_rand(10000, 1000000000));
    }

    public function generatePassword(){
        return substr(md5(time()), 0, 8);
    }

    public function getSalt(){
        return $this->salt;
    }

    public function eraseCredentials(){

    }

    /**
     * @param $param
     * @return $this
     */
    public function setTitle($param){
        $this->title = $param;

        return $this;
    }

    /**
     * @param $param
     * @return $this
     */
    public function setPhone($param){
        $this->phone = $param;

        return $this;
    }

    /**
     * @param $param
     * @return $this
     */
    public function setMobile($param){
        $this->mobile = $param;

        return $this;
    }

    /**
     * @param $param
     * @return $this
     */
    public function setNmls($param){
        $this->nmls = $param;

        return $this;
    }

    /**
     * @param $param
     * @return $this
     */
    public function setSalesDirector($param){
        $this->sales_director = $param;

        return $this;
    }

    /**
     * @param $param
     * @return $this
     */
    public function setSalesDirectorEmail($param){
        $this->sales_director_email = $param;

        return $this;
    }

    public function getPublicInfo(){
        $result = $this->toArray();
        unset($result['password'], $result['salt'], $result['state'], $result['lender'], $result['address']);
        $result['lender'] = $this->getLender()->toArray();
        $address = $this->getAddress();
        if ($address == null) {
            $address = new Address();
        }
        $result['address'] = $address->toArray();
        return $result;
    }

    public function getTitle(){
        return $this->title;
    }

    public function getSalesDirector(){
        return $this->sales_director;
    }

    public function getSalesDirectorEmail(){
        return $this->sales_director_email;
    }

    public function getPhone(){
        return $this->phone;
    }

    public function getMobile(){
        return $this->mobile;
    }

    public function getNmls(){
        return $this->nmls;
    }

    /**
     * @return Address|null
     */
    public function getAddress()
    {
        if($this->address == null) {
            $this->address = new Address();
        }
        return $this->address;
    }

    /**
     * @param Address $address
     */
    public function setAddress(Address $address)
    {
        $this->address = $address;
    }

    /**
     * @return mixed
     */
    public function getSalesDirectorPhone()
    {
        return $this->sales_director_phone;
    }

    /**
     * @param mixed $sales_director_phone
     */
    public function setSalesDirectorPhone($sales_director_phone)
    {
        $this->sales_director_phone = $sales_director_phone;
    }

}