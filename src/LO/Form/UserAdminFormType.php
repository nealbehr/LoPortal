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

class UserAdminFormType extends UserFormType {

    public function buildForm(FormBuilderInterface $builder, array $options) {

        parent::buildForm($builder, $options);

        $builder->add('roles', 'collection', [
            'allow_add' => true,
            'allow_delete' => true,
            'constraints' => [
                new Assert\Choice(['choices' => User::getAllowedRoles(), 'multiple' => true]),
            ]
        ])

        ->add('sales_director', 'text', [
              'constraints' => [
                new Assert\Length([
                    'max' => 255,
                    'maxMessage' => 'Sales director must be shorter than {{ limit }} chars.',
                ])
            ]
        ])

        ->add(
            'sales_director_email',
            'text',
            [
                'constraints' => [
                    new Assert\Regex([
                        'pattern' => self::PATTERN_EMAIL,
                        'message' => 'This value is not a valid email address.'
                    ])
                ]
            ]
        )

        ->add('sales_director_phone', 'text', [
                'constraints' => [
                    new Assert\Length([
                        'max' => 100,
                        'maxMessage' => 'Sales director phone must be shorter than {{ limit }} chars.',
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^[0-9+\(\)#\.\s\/ext-]+$/',
                        'message' => 'Please input a valid US phone number including 3 digit area code and 7 digit number.'
                    ])
                ]
        ]);
    }
} 