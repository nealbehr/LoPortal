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
    const ARCHIVE_CATEGORY = '0';

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

    /**
     * @Column(type="string")
     */
    protected $admin_name;

    /**
     * @Column(type="string")
     */
    protected $user_name;

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

    public function getAdminName()
    {
        return $this->admin_name;
    }

    public function setAdminName($param)
    {
        $this->admin_name = $param;
        return $this;
    }

    public function getUserName()
    {
        return $this->user_name;
    }

    public function setUserName($param)
    {
        $this->user_name = $param;
        return $this;
    }
}
