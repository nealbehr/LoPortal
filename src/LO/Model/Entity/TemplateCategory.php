<?php
/**
 * User: Eugene Lysenko
 * Date: 12/22/15
 * Time: 15:45
 */
namespace LO\Model\Entity;

/**
 * @Entity
 * @Table(name="template_category")
 */
class TemplateCategory
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
    protected $name;

    public function getId()
    {
        return $this->id;
    }

    public function setId($param)
    {
        $this->id = $param;
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
}
