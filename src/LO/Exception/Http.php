<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 3/17/15
 * Time: 11:49 AM
 */
namespace LO\Exception;

use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class Http extends \Exception implements HttpExceptionInterface
{
    public function getStatusCode()
    {
        return $this->code;
    }

    public function getHeaders()
    {
        return [];
    }
}
