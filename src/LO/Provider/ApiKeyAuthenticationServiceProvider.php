<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 3/16/15
 * Time: 11:31 AM
 * ApiKeyAuthenticator for the Symfony Security Component
 */

namespace LO\Provider;

use Silex\Application,
    Silex\ServiceProviderInterface;
use Symfony\Component\Security\Core\Authentication\Provider\SimpleAuthenticationProvider,
    Symfony\Component\Security\Http\Firewall\SimplePreAuthenticationListener;


use LO\Security\ApiKeyAuthenticator;

class ApiKeyAuthenticationServiceProvider implements ServiceProviderInterface
{
    public function register(Application $app)
    {
        $app['security.apikey.authenticator'] = $app->protect(function () use ($app) {
            return new ApiKeyAuthenticator(
                $app['security.user_provider.apikey'](),
                'x-session-token',
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

        return true;
    }

    public function boot(Application $app)
    {
    }

}