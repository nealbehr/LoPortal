<?php namespace LO\Controller;

use Symfony\Component\HttpFoundation\Request;

class AdminUserControllerTest extends \PHPUnit_Framework_TestCase
{
    public function createApplication() {
        $app = (new \LO\Application(['prod', 'test'], __DIR__.'/../../../../../../config/'))->bootstrap()->initRoutes();
        unset($app['exception_handler']);
        $app['session.test'] = true;
        $app['debug.test']   = true;
        $app->boot();

        return $app;
    }

    public function testAddUser() {
        $app = $this->createApplication();
        $app['admin.controller'] = $app->share(function() use ($app) {
            return new Admin\AdminUserController();
        });

        $request = new Request();

        $user = 'a:13:{s:8:"isLogged";s:5:"false";s:14:"sales_director";s:0:"";s:20:"sales_director_email";s:0:"";s:20:'
            .'"sales_director_phone";s:0:"";s:8:"password";a:2:{s:8:"password";s:0:"";s:16:"password_confirm";s:0:"";}'
            .'s:5:"roles";a:1:{i:0;s:9:"ROLE_USER";}s:8:"switched";s:5:"false";s:7:"address";a:5:{s:8:"place_id";s:27:'
            .'"ChIJ_7mUR165woARxQlc1V2IvEk";s:6:"street";s:17:"Santa Monica Blvd";s:4:"city";s:11:"Los Angeles";s:5:'
            .'"state";s:2:"CA";s:17:"formatted_address";s:39:"Santa Monica Blvd, Los Angeles, CA, USA";}s:14:'
            .'"addressOptions";a:2:{s:5:"types";a:1:{i:0;s:7:"geocode";}s:21:"componentRestrictions";a:1:{s:7:"country"'
            .';s:2:"US";}}s:6:"lender";a:2:{s:2:"id";s:1:"1";s:4:"name";s:15:"Banc Home Loans";}s:10:"first_name";s:5:'
            .'"First";s:9:"last_name";s:4:"Last";s:5:"email";s:13:"test@test.com";}';
        $user = array('user' => unserialize($user));

        $this->assertEquals(
            '"success"',
            @$app['admin.controller']->addUserAction($app, $request->create('/admin/user', 'POST', $user))->getContent()
        );
    }
} 