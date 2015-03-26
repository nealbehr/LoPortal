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

class Base {
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

    public function __construct() {
        $this->created_at = new \DateTime();
        $this->updated_at = new \DateTime();
    }

    public function toArray(){
        return get_object_vars($this);
    }

    public function fillFromArray(array $param){
        foreach($this->toArray() as $k => $v){
            if(isset($param[$k])){
                $this->$k = $param[$k];
            }
        }

        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
}