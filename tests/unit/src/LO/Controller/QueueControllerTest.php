<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 6/6/15
 * Time: 6:38 PM
 */

namespace LO\Controller;

use LO\Model\Entity\Queue;
use LO\Model\Entity\User;
use \Mockery as m;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class QueueControllerTest extends \PHPUnit_Framework_TestCase{
    public function testCancelDidNotFindQueueById(){
        $this->setExpectedException('\LO\Exception\Http');

        $repository = m::mock('\Doctrine\ORM\EntityRepository');
        $repository
            ->shouldReceive('findOneBy')
            ->andReturn(false);

        $em = m::mock('\Doctrine\ORM\EntityManager');
        $em->shouldReceive('getRepository')
            ->andReturn($repository);

        $app = m::mock('\LO\Application');

        $app->shouldReceive('getEntityManager')
            ->andReturn($em);

        $tokenInterface = m::mock(TokenInterface::class);
        $tokenInterface->shouldReceive('getUser')->andReturn(new User());

        $tokenStorage = new TokenStorage();
        $tokenStorage->setToken($tokenInterface);

        $app->shouldReceive('getSecurityTokenStorage')->andReturn($tokenStorage);

        $controller = new QueueController();
        $controller->cancelAction($app, 'testId');
    }

    public function testCancel(){
        $repository = m::mock('\Doctrine\ORM\EntityRepository');
        $repository
            ->shouldReceive('findOneBy')
            ->andReturn(new Queue());

        $em = m::mock('\Doctrine\ORM\EntityManager');
        $em->shouldReceive('getRepository')
            ->andReturn($repository);
        $em->shouldReceive('persist');
        $em->shouldReceive('flush');

        $tokenInterface = m::mock(TokenInterface::class);
        $tokenInterface->shouldReceive('getUser')->andReturn(new User());

        $tokenStorage = new TokenStorage();
        $tokenStorage->setToken($tokenInterface);

        $app = m::mock('\LO\Application')->makePartial();

        $app->shouldReceive('getEntityManager')
            ->andReturn($em);

        $app->shouldReceive('getSecurityTokenStorage')->andReturn($tokenStorage);

        $controller = new QueueController();

        $this->assertEquals('"success"', $controller->cancelAction($app, 'testId')->getContent());
    }
} 