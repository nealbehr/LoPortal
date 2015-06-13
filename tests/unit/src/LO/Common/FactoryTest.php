<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 6/13/15
 * Time: 12:55 PM
 */

namespace LO\Common;

use LO\Application;
use LO\Model\Entity\Token;
use LO\Model\Manager\QueueManager;
use \Mockery as m;
use LO\Common\Email\RecoveryPassword;
use LO\Model\Entity\RecoveryPassword as RecoveryPasswordEntity;
use Aws\Ses\SesClient;
use Curl\Curl;

class FactoryTest extends \PHPUnit_Framework_TestCase{
    public function testToken(){
        $this->assertInstanceOf(Token::class, (new Factory())->token());
    }

    public function testRecoveryPassword(){
        $ses = m::mock(SesClient::class);
        $app = m::mock(Application::class);
        $app->shouldReceive('getSes')->andReturn($ses);
        $recoveryPassword = m::mock(RecoveryPasswordEntity::class);

        $this->assertInstanceOf(RecoveryPassword::class, (new Factory())->recoveryPassword($app, 'source', $recoveryPassword));
    }

    public function testCurl(){
        $this->assertInstanceOf(Curl::class, (new Factory())->curl());
    }

    public function testQueueManager(){
        $app = m::mock(Application::class);
        $this->assertInstanceOf(QueueManager::class, (new Factory())->queueManager($app));
    }
} 