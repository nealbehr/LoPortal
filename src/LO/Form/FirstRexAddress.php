<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 5/5/15
 * Time: 5:07 PM
 */

namespace LO\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

class FirstRexAddress extends AbstractType {
    public function getName() {
        return 'address';
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('address', 'text', [
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Address can not be empty.'
                    ]),
                ]
            ])
            ->add('city', 'text', [
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'City can not be empty.'
                    ]),
                ]
            ])
            ->add('state', 'text', [
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'State can not be empty.'
                    ]),
                ]
            ])
            ->add('zip', 'text', [
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Zip can not be empty.'
                    ]),
                ]
            ])
            ->add('apt', 'text', [
                'constraints' => [
                    new Assert\Length([
                        'max'        => 50,
                        'maxMessage' => 'Apartment must be shorter than {{ limit }} chars.',
                    ])
                ]
            ]);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver){
        $resolver->setDefaults([
            'csrf_protection'   => false,
        ]);
    }
} 