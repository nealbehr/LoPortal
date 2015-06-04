<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 6/4/15
 * Time: 11:48 AM
 */

namespace LO\Form;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class UserFormChangePassword extends UserFormType {
    public function buildForm(FormBuilderInterface $builder, array $options) {
        parent::buildForm($builder, $options);

        $builder->add('password', 'repeated', array(
            'type' => 'password',
            'invalid_message' => 'The password fields must match.',
            'first_name' => 'password',
            'second_name' => 'password_confirm',
            'constraints' => [
                new Assert\NotBlank(['message' => 'Password cant\'t be null.']),
            ],
        ));
    }
} 