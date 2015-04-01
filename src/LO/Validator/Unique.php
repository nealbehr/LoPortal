<?php
namespace LO\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class Unique extends Constraint {

    public $notUniqueMessage = '%string% has already been used.';
    public $entity;
    public $field;

    public function validatedBy()
    {
        return 'validator.unique';
    }
} 