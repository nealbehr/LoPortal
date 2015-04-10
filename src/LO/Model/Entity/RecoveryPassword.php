<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 4/10/15
 * Time: 7:10 PM
 */

namespace LO\Model\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\OneToOne;
use Symfony\Component\Validator\ExecutionContextInterface;

/**
 * @Entity
 * @Table(name="recovery_password")
 */
class RecoveryPassword extends Base{
    /**
     * @Column(type="integer")
     * @Assert\NotBlank(message="User id should not be blank.")
     * @Assert\Type(type="numeric")
     */
    protected $user_id;

    /**
     * @Column(type="datetime")
     * @Assert\NotBlank(message="Date expire should not be blank.")
     */
    protected $date_expire;

    /**
     * @Column(type="string", length=32)
     * @Assert\NotBlank(message="Signature expire should not be blank.")
     */
    protected $signature;

    /**
     * @var User
     * @OneToOne(targetEntity="User")
     * @JoinColumn(name="user_id", referencedColumnName="id")
     **/
    private $user;

    public function __construct(){
        $this->generateSignature();
    }

    public function generateSignature(){
        $this->signature = md5(time().mt_rand(100, 10000000));

        return $this;
    }

    public function getUser(){
        return $this->user;
    }

    public function setUser(User $user){
        $this->user = $user;

        return $this;
    }

    public function setDateExpire(\DateTime $param){
        $this->date_expire = $param;

        return $this;
    }

    public function getSignature(){
        return $this->signature;
    }

    public function getDateExpire(){
        return $this->date_expire;
    }
} 