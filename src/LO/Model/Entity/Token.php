<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 3/13/15
 * Time: 1:13 PM
 */

namespace LO\Model\Entity;

/**
 * @Entity
 * @Table(name="tokens",
 *      uniqueConstraints={@UniqueConstraint(name="email_unique",columns={"email"})})
 */
class Token {
    /**
     * @Id
     * @Column(type="bigint")
     * @GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @Column(type="integer", nullable=false)
     */
    protected $user_id;

    /**
     * @Column(type="string", nullable=false)
     */
    protected $hash;

    /**
     * @Column(type="datetime")
     */
    protected $expiration_time;

    /**
     * @Column(type="datetime")
     */
    protected $created_at;

    /**
     * @Column(type="datetime")
     */
    protected $updated_at;

    public function __construct(){
        $this->created_at = new \DateTime();
        $this->updated_at = new \DateTime();
        $this->hash       = $this->generateHash();
    }

    public function setUserId($id){
        $this->user_id = $id;

        return $this;
    }

    /**
     * @param \DateTime $time
     * @return $this
     */
    public function setExpirationTime($time){
        $this->expiration_time = $time instanceof \DateTime
                                        ? $time
                                        : (new \DateTime())->modify(sprintf('+%d day', $time));

        return $this;
    }

    public function setHash($hash){
        $this->hash = $hash;

        return $this;
    }

    public function generateHash(){
        return password_hash(time(),PASSWORD_DEFAULT);
    }

    public function getHash(){
        return $this->hash;
    }
}