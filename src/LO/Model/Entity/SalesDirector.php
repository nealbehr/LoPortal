<?php namespace LO\Model\Entity;

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
 * @Table(name="sales_director", uniqueConstraints={@UniqueConstraint(name="email_unique",columns={"email"})})
 */
class SalesDirector extends Base
{
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
     * @Column(type="string", length=255)
     */
    protected $name;

    /**
     * @Column(type="string", length=50)
     */
    protected $email;

    /**
     * @Column(type="string", length=100)
     */
    protected $phone;

    public function getDeleted()
    {
        return $this->deleted;
    }

    public function setDeleted($param)
    {
        $this->deleted = $param;

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($param)
    {
        $this->name = $param;

        return $this;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($param)
    {
        $this->email = $param;

        return $this;
    }

    public function getPhone()
    {
        return $this->phone;
    }

    public function setPhone($param)
    {
        $this->phone = $param;

        return $this;
    }
}
