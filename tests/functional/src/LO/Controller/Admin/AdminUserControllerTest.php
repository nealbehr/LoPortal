<?php namespace LO\Controller;

use Symfony\Component\HttpFoundation\Request;
use Silex\WebTestCase;

class AdminUserControllerTest extends WebTestCase
{
    private $user;

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
        error_reporting(E_ALL ^ E_USER_DEPRECATED);
        $this->app->getEntityManager()->getConnection()->executeQuery(
            file_get_contents(__DIR__.'/../../../../dump.sql')
        );
        $this->user = $this->getUserData();
    }

    public function getUserData()
    {
        return array(
            'user' => array(
                'isLogged' => 'false',
                'sales_director' => '',
                'sales_director_email' => '',
                'sales_director_phone' => '',
                'password' => array(
                    'password' => '',
                    'password_confirm' => '',
                ),
                'roles' => array('ROLE_USER'),
                'switched' => 'false',
                'address' => array(
                    'place_id' => 'ChIJ_7mUR165woARxQlc1V2IvEk',
                    'street' => 'Santa Monica Blvd',
                    'city' => 'Los Angeles',
                    'state' => 'CA',
                    'formatted_address' => 'Santa Monica Blvd, Los Angeles, CA, USA'
                ),
                'addressOptions' => array(
                    'types' => array('geocode'),
                    'componentRestrictions' => array('country' => 'US')
                ),
                'lender' => array(
                    'id' => '1',
                    'name' => 'Banc Home Loans'
                ),
                'first_name' => 'First',
                'last_name' => 'Last',
                'email' => 'test@test.com'
            )
        );
    }

    public function testAddUser() {
        $controller = new Admin\AdminUserController();
        $request    = new Request();

        $this->assertEquals(
            '{"id":"101"}',
            $controller->addUserAction($this->app, $request->create('/admin/user', 'POST', $this->user))->getContent()
        );
    }

    public function testAddUserWithInvalidPhone() {
        $controller = new Admin\AdminUserController();
        $request    = new Request();

        $data                  = $this->user;
        $data['user']['email'] = 'admin@1rex.com';

        $this->assertEquals(
            '{"form_errors":["","Email address is already registered.\n"],"message":"User info not valid."}',
            $controller->addUserAction($this->app, $request->create('/admin/user', 'POST', $data))->getContent()
        );
    }
} 