<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 3/25/15
 * Time: 2:54 PM
 */
namespace LO\Model\Entity;

use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\MappedSuperclass;
use Doctrine\ORM\Mapping\PrePersist;
use Doctrine\ORM\Mapping\PreUpdate;

/**
 * Base
 *
 * @MappedSuperclass
 * @HasLifecycleCallbacks
 */
class Base
{
    /**
     * @Id
     * @Column(type="bigint")
     * @GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @Column(type="datetime")
     */
    protected $created_at;

    /**
     * @Column(type="datetime")
     */
    protected $updated_at;

    public function __construct()
    {

    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $param
     * @return $this
     */
    public function setId($param)
    {
        $this->id = $param;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * @param $param
     * @return $this
     */
    public function setCreatedAt($param)
    {
        $this->created_at = $param;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

    /**
     * @param $updated_at
     * @return $this
     */
    public function setUpdatedAt($param)
    {
        $this->updated_at = $param;
        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return get_object_vars($this);
    }

    /**
     * @param array $param
     * @return $this
     */
    public function fillFromArray(array $param)
    {
        foreach ($this->toArray() as $k => $v) {
            if (isset($param[$k])) {
                $this->$k = $param[$k];
            }
        }

        return $this;
    }

    /**
     * Now we tell doctrine that before we persist or update we call the updatedTimestamps() function.
     *
     * @PrePersist
     * @PreUpdate
     */
    public function updatedTimestamps()
    {
        $currentDate = new \DateTime('now');
        $this->setUpdatedAt($currentDate);

        if ($this->getCreatedAt() == null) {
            $this->setCreatedAt($currentDate);
        }
    }
}