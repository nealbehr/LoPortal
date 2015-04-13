<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 4/8/15
 * Time: 5:21 PM
 */

namespace LO\Traits;

use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\ConstraintViolation;

trait GetEntityErrors {
    protected function getValidationErrors(ConstraintViolationListInterface $errors){
        $data = [];
        /** @var ConstraintViolation $error */
        foreach($errors as $error){
            $data[] = [$error->getPropertyPath() => $error->getMessage()];
        }

        return $data;
    }
}