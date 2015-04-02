<?php
namespace LO\Provider;

use Knp\Bundle\PaginatorBundle\Helper\Processor;
use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Bundle\FrameworkBundle\Templating\Helper\RouterHelper;
use Symfony\Component\HttpKernel\KernelEvents;
use Knp\Component\Pager\Paginator as KnpPaginator;
use Knp\Bundle\PaginatorBundle\Subscriber\SlidingPaginationSubscriber;
use Knp\Bundle\PaginatorBundle\Twig\Extension\PaginationExtension;
use \Knp\Component\Pager\Event\Subscriber\Paginate\Doctrine\ORM\QuerySubscriber\UsesPaginator;

class Paginator implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function boot(Application $app){
        $app['dispatcher']->addSubscriber($app['paginator.subscriber.orm_querybuilder']);

        $app['dispatcher']->addListener(
            KernelEvents::REQUEST,
            array($app['paginator.subscriber'], 'onKernelRequest'),
            0
        );
        $app['dispatcher']->addSubscriber($app['paginator.subscriber']);
    }

    /**
     * {@inheritdoc}
     */
    public function register(Application $app)
    {
        $app['paginator'] = $app->share(function() use ($app) {
            return new KnpPaginator($app['dispatcher']);
        });

        $app['paginator.subscriber'] = $app->share(function() use ($app) {
            return new SlidingPaginationSubscriber(array(
                "defaultPaginationTemplate" => $app['knp_paginator']['template']['pagination'],
                "defaultSortableTemplate"   => $app['knp_paginator']['template']['sortable'],
                "defaultFiltrationTemplate" => null, /* Not implemented */
                "defaultPageRange"          => $app['knp_paginator']['page_range'],
            ));
        });

        $app['paginator.subscriber.orm_querybuilder'] = $app->share(function() use ($app) {
            return new UsesPaginator();
        });

        $app['paginator.twig.extension'] = $app->share(function() use ($app) {
            return new PaginationExtension(new Processor(new RouterHelper($app['url_generator']), $app['translator']));
        });
    }
}