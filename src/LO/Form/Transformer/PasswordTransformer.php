<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 6/4/15
 * Time: 12:52 PM
 */

namespace LO\Form\Transformer;


use LO\Application;
use LO\Model\Entity\User;
use Symfony\Component\Form\DataTransformerInterface;

class PasswordTransformer implements DataTransformerInterface{
    private $user;
    private $app;

    public function __construct(Application $app, User $user){
        $this->user = $user;
        $this->app = $app;
    }

    public function transform($value){
        return $value;
    }

    public function reverseTransform($value){
        $this->user->setSalt($this->user->generateSalt());
        return $this->app->encodePassword($this->user, $value);
    }
} 