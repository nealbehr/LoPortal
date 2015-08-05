<?php
/**
 * Created by IntelliJ IDEA.
 * User: mac
 * Date: 5/28/15
 * Time: 15:44
 */

namespace LO\Form;

use LO\Model\Entity\Address;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

class AddressType extends AbstractType {

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'address';
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('formatted_address', 'text', [
            'constraints' => [
                new Assert\Length([
                    'max' => 255,
                    'maxMessage' => 'Address name must be shorter than {{ limit }} chars.',
                ])
            ]
        ]);

        $builder->add('place_id', 'text', [
            'constraints' => [
                new Assert\Length([
                    'max' => 100,
                    'maxMessage' => 'Place id must be shorter than {{ limit }} chars.',
                ])
            ]
        ]);

        $builder->add('street_number', 'text', [
            'constraints' => [
                new Assert\Length([
                    'max' => 64,
                    'maxMessage' => 'Street Number must be shorter than {{ limit }} chars.',
                ])
            ]
        ]);

        $builder->add('street', 'text', [
            'constraints' => [
                new Assert\Length([
                    'max' => 64,
                    'maxMessage' => 'Street must be shorter than {{ limit }} chars.',
                ])
            ]
        ]);

        $builder->add('city', 'text', [
            'constraints' => [
                new Assert\Length([
                    'max' => 30,
                    'maxMessage' => 'City must be shorter than {{ limit }} chars.',
                ])
            ]
        ]);

        $builder->add('state', 'text', [
            'constraints' => [
                new Assert\Length([
                    'max' => 2,
                    'maxMessage' => 'State name must be shorter than {{ limit }} chars.',
                ])
            ]
        ]);

        $builder->add('postal_code', 'text', [
            'constraints' => [
                new Assert\Length([
                    'max' => 30,
                    'maxMessage' => 'Postal Code must be shorter than {{ limit }} chars.',
                ])
            ]
        ]);

        $builder->add('apartment', 'text', [
            'constraints' => [
                new Assert\Length([
                    'max'        => 50,
                    'maxMessage' => 'Postal Code must be shorter than {{ limit }} chars.',
                ])
            ]
        ]);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver){
        $resolver->setDefaults([
            'data_class' => Address::class,
            'csrf_protection' => false,
            'allow_extra_fields' => true,
            'validation_groups' => ['Default'],
        ]);
    }
} 