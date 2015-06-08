<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 6/6/15
 * Time: 6:35 PM
 */

namespace LO\Common;

class MessageTest extends \PHPUnit_Framework_TestCase{
    /** @var Message  */
    private $message;

    public function setUp(){
        $this->message = new Message();
    }

    public function testGetWithClearData(){
        $this->message->set([]);
        $this->message->add('ffff');
        $this->message->get();
        $this->assertTrue(count($this->message->get()) == 0);
    }

    public function testGetWithoutClearData(){
        $this->message->set([]);
        $this->message->add('ffff');
        $this->message->get(false);
        $this->assertTrue(count($this->message->get()) > 0);
    }

    public function testSet(){
        $this->assertInstanceOf('\LO\Common\Message', $this->message->set(['ff']));
        $this->assertTrue(count($this->message->get()) == 1);
    }

    public function testAdd(){
        $this->message->set([]);
        $this->assertInstanceOf('\LO\Common\Message', $this->message->add('ff'));
        $this->assertTrue(count($this->message->get()) == 1);
    }

    public function testReplace(){
        $key = 'g';
        $newVal = 'k';

        $this->message->set([$key => 'f']);

        $this->message->replace($key, $newVal);

        $data = $this->message->get();

        $this->assertTrue(isset($data[$key]));
        $this->assertEquals($newVal, $data[$key]);
    }

    public function testMerge(){
        $key = 'g';
        $val1 = 'g';
        $val2 = 'a';
        $keyNew = 'd';

        $this->message->set([$key => 'f']);
        $this->message->merge([$key => $val1, $keyNew => $val2]);

        $data = $this->message->get();

        $this->assertTrue(isset($data[$key]));
        $this->assertEquals($val1, $data[$key]);

        $this->assertTrue(isset($data[$keyNew]));
        $this->assertEquals($val2, $data[$keyNew]);
    }

} 