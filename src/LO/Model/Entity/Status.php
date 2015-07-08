<?php namespace LO\Model\Entity;

/**
 * @Entity
 * @Table(name="status")
 */
class Status
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
    protected $type;

    /**
     * @Column(type="string")
     */
    protected $name;

    /**
     * @Column(type="string")
     */
    protected $text;

    public function getId()
    {
        return $this->id;
    }

    public function setId($param)
    {
        $this->id = $param;

        return $this;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($param)
    {
        $this->type = $param;

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

    public function getText()
    {
        return $this->text;
    }

    public function setText($param)
    {
        $this->text = $param;

        return $this;
    }
}
