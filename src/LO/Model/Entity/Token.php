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

}