<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 6/12/15
 * Time: 11:21 AM
 */

namespace LO\Controller;

use \Mockery as m;
use Symfony\Component\HttpFoundation\Response;

class PartialTest extends \PHPUnit_Framework_TestCase{
    public function testGetActionTemplateNotFound(){
        $twig = m::mock('\Twig_Environment');
        $twig->shouldReceive('render')->andThrow('\Twig_Error_Loader');

        $log = m::mock('\Monolog\Logger');
        $log->shouldReceive('addError');

        $app = m::mock('\LO\Application');
        $app->shouldReceive('getTwig')->andReturn($twig);
        $app->shouldReceive('getMonolog')->andReturn($log);

        $controller = new Partial();
        /** @var Response $res */
        $res = $controller->getAction($app, 'login');

        $this->assertTrue($res instanceof Response);
        $this->assertEquals('Template \'login\' not found.', $res->getContent());
    }

    public function testGetAction(){
        $twig = m::mock('\Twig_Environment');
        $twig->shouldReceive('render')->andReturn("template body");

        $app = m::mock('\LO\Application');
        $app->shouldReceive('getTwig')->andReturn($twig);

        $controller = new Partial();
        /** @var Response $res */
        $res = $controller->getAction($app, 'login');

        $this->assertTrue($res instanceof Response);
        $this->assertEquals('template body', $res->getContent());
    }
} 