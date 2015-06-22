<?php namespace LO\Controller;

use Silex\WebTestCase;
use \LO\Model\Entity\SalesDirector;
use Symfony\Component\HttpFoundation\Request;

class AdminUserControllerTest extends WebTestCase
{
    private $showDeprecated = false;

    public function createApplication()
    {
        $app = (new \LO\Application(['prod', 'test'], __DIR__.'/../../../../../../config/'))->bootstrap()->initRoutes();
        unset($app['exception_handler']);
        $app['session.test'] = true;
        $app['debug.test']   = true;
        $app->boot();

        return $app;
    }

    public function setUp()
    {
        parent::setUp();

        if (!$this->showDeprecated) {
            error_reporting(E_ALL ^ E_USER_DEPRECATED);
        }

        $this->app->getEntityManager()->getConnection()->executeQuery(
            file_get_contents(__DIR__.'/../../../../dump.sql')
        );
    }

    public function testAdd()
    {
        $data = ['salesDirector' => (new SalesDirector())->setName('Test Name')->setEmail('test@gmail.com')->toArray()];
        $data = json_decode(
            (new Admin\SalesDirectorController())->addAction(
                $this->app,
                (new Request())->create('/admin/salesdirector', 'POST', $data)
            )->getContent(),
            true
        );

        $this->assertEquals('2', $data['id']);
    }

    public function testUpdate()
    {
        $data = ['salesDirector' => (new SalesDirector())->setName('Test Na-me')->setEmail('test@mail.com')->toArray()];
        $this->assertEquals(
            'success',
            json_decode(
                (new Admin\SalesDirectorController())->updateAction(
                    $this->app,
                    (new Request())->create('/admin/salesdirector', 'PUT', $data),
                    '1'
                )->getContent(),
                true
            )
        );
    }

    public function testDelete()
    {
        $this->assertEquals(
            'success',
            json_decode((new Admin\SalesDirectorController())->deleteAction($this->app, '1')->getContent(), true)
        );
    }
}
