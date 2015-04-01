<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 4/1/15
 * Time: 3:38 PM
 */

namespace LO\Form;

use LO\Model\Entity\User;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class UserAdminForm extends UserForm{
    public function buildForm(FormBuilderInterface $builder, array $options) {
        parent::buildForm($builder, $options);
        $builder->add('roles', 'collection', [
            'allow_add' => true,
            'allow_delete' => true,
            'constraints' => [
                new Assert\Choice(['choices' => User::getAllowedRoles(), 'multiple' => true]),
            ]
        ]);
    }
} 