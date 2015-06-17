<?php namespace LO\Model\Entity;

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
