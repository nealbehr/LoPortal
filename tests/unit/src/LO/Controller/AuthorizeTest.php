<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 6/12/15
 * Time: 5:23 PM
 */

namespace LO\Controller;

use LO\Model\Entity\RecoveryPassword;
use LO\Model\Entity\Token;
use LO\Model\Entity\User;
use LO\Security\CryptDigestPasswordEncoder;
use \Mockery as m;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;

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

    public function testAutocompleteAction(){
        $result = ['s.samoilenko@gmail.com'];

        $configuration = m::mock('\Doctrine\ORM\Configuration');
        $configuration->shouldReceive('addCustomHydrationMode');

        $query = m::mock('Doctrine\ORM\AbstractQuery');
        $query->shouldReceive('execute')->andReturn($result);

        $em = m::mock('Doctrine\ORM\EntityManager')->makePartial();

        $queryBuilder = m::mock('Doctrine\ORM\QueryBuilder', [$em])->makePartial();

        $queryBuilder->shouldReceive('getQuery')->andReturn($query);


        $em->shouldReceive('getConfiguration')->andReturn($configuration);
        $em->shouldReceive('createQueryBuilder')->andReturn($queryBuilder);

        $app = m::mock('LO\Application')->makePartial();
        $app->shouldReceive('getEntityManager')->andReturn($em);

        $res = (new Authorize())->autocompleteAction($app, 's.samoi');

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $res);
        $this->assertEquals(json_encode($result), $res->getContent());
    }

    public function testResetPasswordUserNotFound(){
        $em = m::mock('Doctrine\ORM\EntityManager')->makePartial();
        $em->shouldReceive('beginTransaction');
        $em->shouldReceive('rollback');

        $userManager = m::mock('LO\Model\Manager\UserManager');
        $userManager->shouldReceive('findByEmail')->andReturn(null);

        $app = m::mock('LO\Application')->makePartial();
        $app->shouldReceive('getEntityManager')->andReturn($em);
        $app->shouldReceive('getUserManager')->andReturn($userManager);

        $res = (new Authorize())->resetPasswordAction($app, 'some@email.com');

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $res);
        $this->assertEquals(404, $res->getStatusCode());
    }

    public function testResetPassword(){
        $em = m::mock('Doctrine\ORM\EntityManager')->makePartial();
        $em->shouldReceive('beginTransaction');
        $em->shouldReceive('commit');
        $em->shouldReceive('persist');
        $em->shouldReceive('flush');

        $userManager = m::mock('LO\Model\Manager\UserManager');
        $userManager->shouldReceive('findByEmail')->andReturn(new User());

        $recoveryPassword = m::mock('LO\Common\Email\RecoveryPassword')->makePartial();
        $recoveryPassword->shouldReceive('send');

        $factory = m::mock('LO\Common\Factory');
        $factory->shouldReceive('recoveryPassword')->andReturn($recoveryPassword);

        $app = m::mock('LO\Application')->makePartial();
        $app->shouldReceive('getEntityManager')->andReturn($em);
        $app->shouldReceive('getUserManager')->andReturn($userManager);
        $app->shouldReceive('getConfigByName');
        $app->shouldReceive('getFactory')->andReturn($factory);

        $res = (new Authorize())->resetPasswordAction($app, 'some@email.com');

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $res);
        $this->assertEquals(200, $res->getStatusCode());
    }

    public function testConfirmPasswordBadParameters(){
        $em = m::mock('Doctrine\ORM\EntityManager')->makePartial();
        $em->shouldReceive('beginTransaction');
        $em->shouldReceive('rollback');


        $query = m::mock('Doctrine\ORM\AbstractQuery');
        $query->shouldReceive('getOneOrNullResult')->andReturn(null);

        $queryBuilder = m::mock('Doctrine\ORM\QueryBuilder', [$em])->makePartial();

        $queryBuilder->shouldReceive('getQuery')->andReturn($query);

        $em->shouldReceive('createQueryBuilder')->andReturn($queryBuilder);

        $log = m::mock('\Monolog\Logger');
        $log->shouldReceive('addWarning');

        $app = m::mock('LO\Application')->makePartial();
        $app->shouldReceive('getEntityManager')->andReturn($em);
        $app->shouldReceive('getMonolog')->andReturn($log);

        $res = (new Authorize())->confirmPassword($app, new Request(), 19);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $res);
        $this->assertEquals(400, $res->getStatusCode());
    }

    public function testConfirmPasswordDateExpire(){
        $em = m::mock('Doctrine\ORM\EntityManager')->makePartial();
        $em->shouldReceive('beginTransaction');
        $em->shouldReceive('rollback');
        $em->shouldReceive('remove');
        $em->shouldReceive('flush');


        $query = m::mock('Doctrine\ORM\AbstractQuery');
        $query->shouldReceive('getOneOrNullResult')->andReturn((new RecoveryPassword())->setDateExpire((new \DateTime())->modify('-4 day')));

        $queryBuilder = m::mock('Doctrine\ORM\QueryBuilder', [$em])->makePartial();

        $queryBuilder->shouldReceive('getQuery')->andReturn($query);

        $em->shouldReceive('createQueryBuilder')->andReturn($queryBuilder);

        $log = m::mock('\Monolog\Logger');
        $log->shouldReceive('addWarning');

        $app = m::mock('LO\Application')->makePartial();
        $app->shouldReceive('getEntityManager')->andReturn($em);
        $app->shouldReceive('getMonolog')->andReturn($log);

        $res = (new Authorize())->confirmPassword($app, new Request(), 19);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $res);
        $this->assertEquals(423, $res->getStatusCode());
    }

    public function testConfirmPassword(){
        $em = m::mock('Doctrine\ORM\EntityManager')->makePartial();
        $em->shouldReceive('beginTransaction');
        $em->shouldReceive('commit');
        $em->shouldReceive('remove');
        $em->shouldReceive('flush');
        $em->shouldReceive('persist');

        $user = m::mock(User::class)->makePartial();
        $user->shouldReceive('generatePassword')->andReturn('123');
        $user->shouldReceive('generateSalt')->andReturn('qwe');

        $recoveryPassword = m::mock(RecoveryPassword::class)->makePartial();
        $recoveryPassword->setDateExpire((new \DateTime())->modify('+4 day'));
        $recoveryPassword->shouldReceive('getUser')->andReturn($user);


        $query = m::mock('Doctrine\ORM\AbstractQuery');
        $query->shouldReceive('getOneOrNullResult')->andReturn($recoveryPassword);

        $queryBuilder = m::mock('Doctrine\ORM\QueryBuilder', [$em])->makePartial();

        $queryBuilder->shouldReceive('getQuery')->andReturn($query);

        $em->shouldReceive('createQueryBuilder')->andReturn($queryBuilder);

        $cryptDigestPasswordEncoder = new CryptDigestPasswordEncoder();

        $app = m::mock('LO\Application')->makePartial();
        $app->shouldReceive('getEntityManager')->andReturn($em);
        $app->shouldReceive('getEncoderDigest')->andReturn($cryptDigestPasswordEncoder);

        $res = (new Authorize())->confirmPassword($app, new Request(), 19);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\JsonResponse', $res);
        $this->assertEquals(200, $res->getStatusCode());
        $this->assertContains(json_decode($user->getPassword()), $res->getContent());
    }
}