<?php namespace LO\Controller;

use Silex\WebTestCase;
use \LO\Model\Entity\Realtor;
use Symfony\Component\HttpFoundation\Request;

class RealtorControllerTest extends WebTestCase
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
        $data = ['realtor' => (new Realtor())
            ->setFirstName('First')
            ->setLastName('Last')
            ->setRealtyCompanyId('1')
            ->setEmail('test@gmail.com')
            ->setPhone('83478598347')
            ->toArray()];
        $data = json_decode(
            (new Admin\RealtorController())->addAction(
                $this->app,
                (new Request())->create('/admin/realtor', 'POST', $data)
            )->getContent(),
            true
        );

        $this->assertEquals('2', $data['id']);
    }


    public function testCompanyDoesNotExist() {
        $data = ['realtor' => (new Realtor())
            ->setFirstName('First')
            ->setLastName('Last')
            ->setRealtyCompanyId('9999999')
            ->setEmail('test@gmail.com')
            ->setPhone('83478598347')
            ->toArray()];
        $data = json_decode(
            (new Admin\RealtorController())->addAction(
                $this->app,
                (new Request())->create('/admin/realtor', 'POST', $data)
            )->getContent(),
            true
        );

        $this->assertEquals(false, isset($data['id']));
    }

    public function testUpdate()
    {
        $idTest = 1;
        $model  = $this->app->getEntityManager()->getRepository(Realtor::class)->find($idTest);
        $data   = ['realtor' => $model->setEmail('test2@mail.com')->toArray()];

        $this->assertEquals(
            'success',
            json_decode(
                (new Admin\RealtorController())->updateAction(
                    $this->app,
                    (new Request())->create('/admin/realtor', 'PUT', $data),
                    $idTest
                )->getContent(),
                true
            )
        );
    }

    public function testDelete()
    {
        $this->assertEquals(
            'success',
            json_decode((new Admin\RealtorController())->deleteAction($this->app, '1')->getContent(), true)
        );
    }
}
