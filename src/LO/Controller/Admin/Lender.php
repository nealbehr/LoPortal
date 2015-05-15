<?php
/**
 * Created by IntelliJ IDEA.
 * User: mac
 * Date: 5/14/15
 * Time: 18:36
 */

namespace LO\Controller\Admin;

use LO\Application;
use Symfony\Component\HttpFoundation\Request;

class Lender extends Base {

    const QUEUE_LIMIT = 20;

    public function getAction(Application $app, Request $request){

        return $app->json();
    }
} 