<?php namespace LO\Model\Entity;

/**
 * @Entity
 * @Table(name="sync_log")
 */
class SyncLog
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
    protected $full_log;

    /**
     * @Column(type="datetime")
     */
    protected $start_time;

    /**
     * @Column(type="datetime")
     */
    protected $end_time;

    public function getId()
    {
        return $this->id;
    }

    public function setId($param)
    {
        $this->id = $param;
        return $this;
    }

    public function getFullLog()
    {
        return $this->full_log;
    }

    public function setFullLog($param)
    {
        $this->full_log = $param;
        return $this;
    }

    public function getStartTime()
    {
        return $this->start_time;
    }

    public function setStartTime($param)
    {
        $this->start_time = $param;
        return $this;
    }

    public function getEndTime()
    {
        return $this->end_time;
    }

    public function setEndTime($param)
    {
        $this->end_time = $param;
        return $this;
    }
}
