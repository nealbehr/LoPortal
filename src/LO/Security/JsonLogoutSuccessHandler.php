<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 3/22/15
 * Time: 2:33 PM
 */

namespace LO\Security;

use LO\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;

class JsonLogoutSuccessHandler implements LogoutSuccessHandlerInterface{
    protected $app;
    protected $response;

    /**
     * @param Application $app
     * @param $response
     */
    public function __construct(Application $app, $response){
        $this->app = $app;
        $this->response = $response;
    }

    /**
     * {@inheritdoc}
     */
    public function onLogoutSuccess(Request $request){
        return $this->app->json($this->response);
    }
}