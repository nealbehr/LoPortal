<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 6/13/15
 * Time: 1:38 PM
 */

namespace LO\Controller;

use LO\Model\Entity\User;
use \Mockery as m;
use LO\Application;
use LO\Model\Manager\DashboardManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class DashboardControllerTest extends \PHPUnit_Framework_TestCase{
    public function testIndexAction(){
        $tokenInterface = m::mock(TokenInterface::class);
        $tokenInterface->shouldReceive('getUser')->andReturn(new User());

        $tokenStorage = new TokenStorage();
        $tokenStorage->setToken($tokenInterface);

        $dashboardManager = m::mock(DashboardManager::class);
        $dashboardManager->shouldReceive('getByUserId');

        $app = m::mock(Application::class)->makePartial();

        $app->shouldReceive('getSecurityTokenStorage')->andReturn($tokenStorage);
        $app->shouldReceive('getDashboardManager')->andReturn($dashboardManager);

        $res = (new DashboardController())->indexAction($app);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $res);
        $this->assertContains('dashboard', $res->getContent());
    }

    public function testGetCollateralAction(){
        $tokenInterface = m::mock(TokenInterface::class);
        $tokenInterface->shouldReceive('getUser')->andReturn(new User());

        $tokenStorage = new TokenStorage();
        $tokenStorage->setToken($tokenInterface);

        $dashboardManager = m::mock(DashboardManager::class);
        $dashboardManager->shouldReceive('getCollateralByUserId')->andReturn(['a' => 'b']);

        $app = m::mock(Application::class)->makePartial();

        $app->shouldReceive('getSecurityTokenStorage')->andReturn($tokenStorage);
        $app->shouldReceive('getDashboardManager')->andReturn($dashboardManager);

        $res = (new DashboardController())->getCollateralAction($app);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $res);
        $this->assertInternalType('array', json_decode($res->getContent(), true));
        $this->assertTrue(isset(json_decode($res->getContent(), true)['a']));
    }
} 