<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 3/12/15
 * Time: 4:10 PM
 */

namespace LO\Controller;

use LO\Application;
use Symfony\Component\HttpFoundation\Response;

class Partial
{
    public function getAction(Application $app, $filename)
    {
        try{
            return new Response($app->getTwig()->render($filename.'.twig'));
        }catch (\Twig_Error_Loader $e){
            $app->getMonolog()->addError($e);
        }


        return (new Response(sprintf('Template \'%s\' not found.', $filename)))->setCache([]);
    }
}
