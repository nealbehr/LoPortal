<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 6/13/15
 * Time: 3:05 PM
 */

namespace LO\Controller;

use LO\Application;
use \Mockery as m;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Monolog\Logger;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

class RequestApprovalControllerTest extends \PHPUnit_Framework_TestCase{
    public function testAddAction(){
        $em = m::mock(EntityManager::class);
        $em->shouldReceive('rollback');
        $em->shouldReceive('beginTransaction');

        $log = m::mock(Logger::class);
        $log->shouldReceive('addError');

        $formInterface = m::mock(FormInterface::class);
        $formInterface->shouldReceive('handleRequest');
        $formInterface->shouldReceive('isValid')->andReturn(false);
        $formInterface->shouldReceive('getErrors')->andReturn("");
        $formInterface->shouldReceive('getIterator')->andReturn(m::mock(\Iterator::class));

        $factoryInterface = m::mock(FormFactoryInterface::class);
        $factoryInterface->shouldReceive('create')->andReturn($formInterface);

        $formFactory = m::mock(FormFactoryInterface::class)->makePartial();
        $formFactory->shouldReceive('getFormFactory')->andReturn($factoryInterface);

        $app = m::mock(Application::class)->makePartial();
        $app->shouldReceive('getEntityManager')->andReturn($em);
        $app->shouldReceive('getMonolog')->andReturn($log);
        $app->shouldReceive('getFormFactory')->andReturn($factoryInterface);

        $controller = m::mock(RequestApprovalController::class)->makePartial();
        $controller->shouldReceive('getFormErrors');

        $res = $controller->AddAction($app, new Request());

        $this->assertInstanceOf(JsonResponse::class, $res);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $res->getStatusCode());

    }
} 