<?php namespace LO\Controller;

use Silex\WebTestCase;
use \LO\Model\Entity\User;
use \LO\Model\Entity\Address;
use Symfony\Component\HttpFoundation\Request;

class AdminUserControllerTest extends WebTestCase
{
    private $user;

    private $showDeprecated = false;

    public function createApplication() {
        $app = (new \LO\Application(['prod', 'test'], __DIR__.'/../../../../../../config/'))->bootstrap()->initRoutes();
        unset($app['exception_handler']);
        $app['session.test'] = true;
        $app['debug.test']   = true;
        $app->boot();

        return $app;
    }

    public function setUp() {
        parent::setUp();

        if (!$this->showDeprecated) {
            error_reporting(E_ALL ^ E_USER_DEPRECATED);
        }

        $this->app->getEntityManager()->getConnection()->executeQuery(
            file_get_contents(__DIR__.'/../../../../dump.sql')
        );
        $this->user = $this->getUserData();
    }

    private function getUserData() {
        $user    = new User();
        $address = new Address;

        $user->setRoles(['ROLE_USER']);
        $user->setLender([
            'id'   => '1',
            'name' => 'Banc Home Loans'
        ]);
        $user->setFirstName('First');
        $user->setLastName('Last');
        $user->setEmail('test@test.com');

        $address->setPlaceId('ChIJ_7mUR165woARxQlc1V2IvEk');
        $address->setStreet('Santa Monica Blvd');
        $address->setCity('Los Angeles');
        $address->setState('CA');
        $address->setFormattedAddress('Santa Monica Blvd, Los Angeles, CA, USA');
        $user->setAddress($address);

        $user            = $user->toArray();
        $user['address'] = $user['address']->toArray();

        return ['user' => $user];
    }

    private function addUser($data) {
        $controller = new Admin\AdminUserController();
        $request    = new Request();

        return json_decode(
            $controller->addUserAction($this->app, $request->create('/admin/user', 'POST', $data))->getContent(),
            true
        );
    }

    public function testUserPhoneInvalid() {
        $data                  = $this->user;
        $data['user']['phone'] = '(510) 339-4300 ext. 106b';
        $data                  = $this->addUser($data);

        $this->assertEquals(true, isset($data['form_errors']));
    }

    public function testAddUser() {
        $data = $this->addUser($this->user);

        $this->assertEquals('101', $data['id']);
    }

    public function testEmailAddressIsAlreadyRegistered() {
        $data                  = $this->user;
        $data['user']['email'] = 'admin@1rex.com';
        $data                  = $this->addUser($data);

        $this->assertEquals(true, isset($data['form_errors']));
    }
}
