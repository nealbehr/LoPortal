<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 3/27/15
 * Time: 2:16 PM
 */

namespace LO\Model\Manager;

use \LO\Application;

class Base {
    /** @var \LO\Application  */
    private $app;

    public function __construct(Application $app){
        $this->app = $app;
    }


    public function getApp(){
        return $this->app;
    }
} 