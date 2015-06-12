<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 6/12/15
 * Time: 5:23 PM
 */

namespace LO\Controller;

use LO\Model\Entity\Token;
use LO\Model\Entity\User;
use LO\Security\CryptDigestPasswordEncoder;
use \Mockery as m;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;

class AuthorizeTest extends \PHPUnit_Framework_TestCase{
    public function testSigninActionUserNotFound(){
        $userManager = m::mock('LO\Model\Manager\UserManager');
        $userManager->shouldReceive('findByEmail')->andReturn(false);

        $app = m::mock('\LO\Application')->makePartial();

        $app->shouldReceive('getUserManager')->andReturn($userManager);

        $res = (new Authorize())->signinAction($app, new Request());

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $res);
        $this->assertEquals('{"message":"Your email address is not recognized"}', $res->getContent());
    }

    public function testSigninActionIncorrectPassword(){
        $userManager = m::mock('LO\Model\Manager\UserManager');
        $userManager->shouldReceive('findByEmail')->andReturn((new User())->setPassword('sajdajksdkjagdshkj'));

        $app = m::mock('\LO\Application')->makePartial();

        $app->shouldReceive('getUserManager')->andReturn($userManager);

        $app->shouldReceive('getEncoderFactory')->andReturn(new EncoderFactory(array(
            'Symfony\Component\Security\Core\User\UserInterface' => new CryptDigestPasswordEncoder(),
        )));

        $res = (new Authorize())->signinAction($app, new Request());

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $res);
        $this->assertEquals('{"message":"Entered password is incorrect"}', $res->getContent());
    }

    public function testSigninAction(){
        $password = '123';

        $userManager = m::mock('LO\Model\Manager\UserManager');
        $userManager->shouldReceive('findByEmail')->andReturn((new User())->setPassword($password)->setId(19));

        $em = m::mock('Doctrine\ORM\EntityManager');
        $em->shouldReceive('persist');
        $em->shouldReceive('flush');

        $token = new Token();

        $factory = m::mock('LO\Common\Factory');
        $factory->shouldReceive('token')->andReturn($token);

        $app = m::mock('\LO\Application')->makePartial();
        $app->shouldReceive('getUserManager')->andReturn($userManager);
        $app->shouldReceive('getEntityManager')->andReturn($em);
        $app->shouldReceive('getConfigByName');
        $app->shouldReceive('getFactory')->andReturn($factory);

        $app->shouldReceive('getEncoderFactory')->andReturn(new EncoderFactory(array(
            'Symfony\Component\Security\Core\User\UserInterface' => new CryptDigestPasswordEncoder(),
        )));

        $request = new Request();
        $request->request->set('password', $password);

        $res = (new Authorize())->signinAction($app, $request);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $res);
        $this->assertInternalType('string', $res->getContent());
        $this->assertEquals(json_encode($token->getHash()), $res->getContent());
    }
}