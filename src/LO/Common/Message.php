<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 5/9/15
 * Time: 4:28 PM
 */

namespace LO\Common;


class Message {
    private $data = [];

    /**
     * @param bool $clear
     * @return array
     */
    public function get($clear = true) {
        try{
            return $this->data;
        }finally{
            if($clear){
                $this->set([]);
            }
        }
    }

    /**
     * @param array $data
     * @return Message
     */
    public function set(array $data) {
        $this->data = $data;

        return $this;
    }

    /**
     * @param $key
     * @param $data
     * @return $this
     */
    public function replace($key, $data) {
        $this->data[$key] = $data;

        return $this;
    }

    /**
     * @param mixed $data
     * @return Message
     */
    public function add($data) {
        $this->data[] = $data;

        return $this;
    }

    /**
     * @param array $data
     * @return Message
     */
    public function merge(array $data) {
        $this->data = array_merge($this->data, $data);

        return $this;
    }


} 