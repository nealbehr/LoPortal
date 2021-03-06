<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 3/26/15
 * Time: 6:52 PM
 */

namespace LO\Provider;

use Aws\Ses\SesClient;
use Silex\Application;
use Silex\ServiceProviderInterface;
use Aws\S3\S3Client;
use Aws\Common\Credentials\Credentials;

class Amazon implements ServiceProviderInterface{

    /**
     * Bootstraps the application.
     *
     * This method is called after all services are registered
     * and should be used for "dynamic" configuration (whenever
     * a service must be requested).
     */
    public function boot(Application $app)
    {
    }

    public function register(Application $app){
        $app['amazon.credential'] = $app->share(function() use ($app){
                return Credentials::factory($app->getConfigByName('amazon', 'securityCredentials'));
            }
        );

        $app['amazon.s3'] = $app->share(function () use ($app) {
                $s3 = S3Client::factory($app->getConfigByName('amazon', 'securityCredentials'));

                return $s3;
            }
        );

        $app['amazon.ses'] = $app->share(function () use ($app) {
                return SesClient::factory($app->getConfigByName('amazon', 'ses', 'securityCredentials'));
        });
    }

}