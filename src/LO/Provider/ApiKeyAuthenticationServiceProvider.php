<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 3/16/15
 * Time: 11:31 AM
 * ApiKeyAuthenticator for the Symfony Security Component
 */

namespace LO\Provider;

use LO\Security\JsonLogoutSuccessHandler;
use LO\Security\TokenLogoutHandler;
use Silex\Application,
    Silex\ServiceProviderInterface;
use Symfony\Component\Security\Core\Authentication\Provider\SimpleAuthenticationProvider,
    Symfony\Component\Security\Http\Firewall\SimplePreAuthenticationListener;


use LO\Security\ApiKeyAuthenticator;
use Symfony\Component\Security\Http\Firewall\LogoutListener;
use Symfony\Component\Security\Http\Logout\CookieClearingLogoutHandler;
use Symfony\Component\Security\Http\Logout\SessionLogoutHandler;

class ApiKeyAuthenticationServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['security.apikey.authenticator'] = $app->protect(function () use ($app) {
            return new ApiKeyAuthenticator(
                $app['security.user_provider.apikey'](),
                $app['logger']
            );
        });

        $app['security.authentication_listener.factory.apikey'] = $app->protect(function ($name, $options) use ($app) {

            $app['security.authentication_provider.'.$name.'.apikey'] = $app->share(function () use ($app, $name) {
                return new SimpleAuthenticationProvider(
                    $app['security.apikey.authenticator'](),
                    $app['security.user_provider.apikey'](),
                    $name
                );
            });

            $app['security.authentication_listener.' . $name . '.apikey'] = $app->share(function () use ($app, $name, $options) {
                return new SimplePreAuthenticationListener(
                    $app['security'],
                    $app['security.authentication_manager'],
                    $name,
                    $app['security.apikey.authenticator'](),
                    $app['logger']
                );
            });

            return array(
                'security.authentication_provider.'.$name.'.apikey',
                'security.authentication_listener.'.$name.'.apikey',
                null,       // entrypoint
                'pre_auth'  // position of the listener in the stack
            );
        });

        $type       = 'apiLogout';

        $app['security.authentication_listener.factory.'.$type] = $app->protect(function ($name, $options) use ($type, $app) {
            $app['security.authentication_listener.'.$name.'.'.$type] = $app->share(function() use ($app, $name, $options, $type){
                $route = isset($options['logout_path']) ? $options['logout_path'] : '/logout';
                $app->delete($route)->run(null)->bind(str_replace('/', '_', ltrim($route, '/')));

                $app['security.authentication.logout_handler.'.$name] = $app->share(function() use ($app){
                    return new JsonLogoutSuccessHandler($app, isset($options['response'])? $options['response']: ['ok']);
                });


                $listener = new LogoutListener(
                    $app['security'],
                    $app['security.http_utils'],
                    $app['security.authentication.logout_handler.'.$name],
                    $options,
                    null
                );

                $listener->addHandler(new TokenLogoutHandler($app));
                $listener->addHandler(new SessionLogoutHandler());
                $listener->addHandler(new CookieClearingLogoutHandler(['access_token' => ['path' => '/', 'domain' => null]]));


                return $listener;
            });

            $app['security.authentication_provider.'.$name.'.'.$type] = $app['security.authentication_provider.dao._proto']($name);

            return array(
                'security.authentication_provider.'.$name.'.'.$type,
                'security.authentication_listener.'.$name.'.'.$type,
                null,
                'logout',
            );
        });

        return true;
    }

    public function boot(Application $app)
    {
    }

}