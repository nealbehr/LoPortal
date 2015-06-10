<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 6/8/15
 * Time: 8:05 PM
 */
namespace LO\Controller\Queue;

use LO\Application;
use LO\Model\Entity\Queue;
use LO\Model\Entity\User;
use LO\Security\ApiKeyAuthenticator;
use Silex\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpKernel\Client;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class QueueControllerTest extends WebTestCase{
    /** @var Client  */
    private $client;

    public function createApplication(){
        $app = (new Application(['prod', 'test'], __DIR__.'/../../../../../config/'))->bootstrap()->initRoutes();
        unset($app['exception_handler']);
        $app['session.test'] = true;
        $app['debug.test'] = true;
        $app->boot();

        return $app;
    }

    public function setUp(){
        parent::setUp();
        $this->client = static::createClient();
        $this->app->getEntityManager()->getConnection()->executeQuery(file_get_contents(__DIR__."/../../../dump.sql"));
    }

    public function testCancelAction(){
        $this->logIn();
        $id = 1;
        $this->client->request('PATCH', '/queue/cancel/'.$id);

        /** @var Queue $queue */
        $queue = $this->app->getEntityManager()->getRepository(Queue::class)->findOneBy(['id' => $id]);

        $this->assertTrue($this->client->getResponse()->isOk());
        $this->assertEquals(Queue::STATE_DECLINED, $queue->getState());
    }

    private function logIn()
    {
        $session = $this->app['session'];

        $firewall = 'api';
        $token = new UsernamePasswordToken($this->app->getEntityManager()->getRepository(User::class)->findOneBy(['id' => 99]), null, $firewall, array('ROLE_ADMIN'));
        $session->set('_security_'.$firewall, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $this->client->getCookieJar()->set($cookie);
    }

} 