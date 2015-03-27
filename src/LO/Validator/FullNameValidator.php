<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 3/25/15
 * Time: 6:08 PM
 */

namespace LO\Validator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class FullNameValidator extends ConstraintValidator{
    public function validate($value, Constraint $constraint){
        if (!preg_match('/^[a-zA-Za\s]+$/', $value, $matches)) {
            $this->context->addViolation(
                $constraint->message,
                array('%string%' => $value)
            );
        }
    }
} 