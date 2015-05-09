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
        $errors = [$this->prepareMessage((string)$form->getErrors())];
        foreach($form as $child){
            if($child->getErrors()->count() > 0){
                $errors[] = $this->prepareMessage((string)$child->getErrors());
            }
        }

        return $errors;
    }

    private function prepareMessage($message){
        return str_replace("ERROR: ", "", $message);
    }

    protected function removeExtraFields($requestData, $form){
        return array_intersect_key($requestData, $form->all());
    }
} 