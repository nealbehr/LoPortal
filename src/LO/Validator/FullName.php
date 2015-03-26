<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 3/25/15
 * Time: 6:06 PM
 */

namespace LO\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * Class FullName
 * @package LO\Validator
 *
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
class FullName extends Constraint{
    public $message = "Name can contain only a-zA-Z";
} 