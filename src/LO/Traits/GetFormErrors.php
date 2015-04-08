<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 4/8/15
 * Time: 4:44 PM
 */

namespace LO\Traits;

use Symfony\Component\Form\FormInterface;

trait GetFormErrors {
    public function getFormErrors(FormInterface $form){
        $errors = [];
        foreach($form as $child){
            if($child->getErrors()->count() > 0){
                $errors[] = str_replace("ERROR: ", "", (string)$child->getErrors());
            }
        }

        return $errors;
    }

    protected function removeExtraFields($requestData, $form){
        return array_intersect_key($requestData, $form->all());
    }
} 