<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 4/1/15
 * Time: 3:38 PM
 */

namespace LO\Form;

use LO\Form\Extension\S3Photo;
use LO\Model\Entity\User;
use LO\Validator\Unique;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Aws\S3\S3Client;

class UserFormType extends AbstractType {
    private $s3;

    public function __construct(S3Client $s3){
        $this->s3 = $s3;
    }

    public function getName() {
        return 'user';
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {

        $builder->add('first_name', 'text', [
            'constraints' => [
                new Assert\Regex([
                    'pattern' => "/^([A-Za-z_\s]+)$/",
                    'message' => 'First name is invalid'
                ]),
                new Assert\Length([
                    'max' => 50,
                    'maxMessage' => 'First name must be shorter than {{ limit }} chars.',
                ])
            ]
        ]);

        $builder->add('last_name', 'text', [
            'constraints' => [
                new Assert\Regex([
                    'pattern' => "/^([A-Za-z_\s]+)$/",
                    'message' => 'Last name is invalid'
                ]),
                new Assert\Length([
                    'max' => 50,
                    'maxMessage' => 'Last name must be shorter than {{ limit }} chars.',
                ])
            ]
        ]);

        $builder->add('title', 'text', [
            'constraints' => [
                new Assert\Length([
                    'max' => 100,
                    'maxMessage' => 'Title must be shorter than {{ limit }} chars.',
                ])
            ]
        ]);

        $builder->add('phone', 'text', [
            'constraints' => [
                new Assert\Length([
                    'max' => 100,
                    'maxMessage' => 'Phone must be shorter than {{ limit }} chars.',
                ]),
                new Assert\Regex([
                    'pattern' => '/^[0-9+\(\)#\.\s\/ext-]+$/',
                    'message' => 'Please input a valid US phone number including 3 digit area code and 7 digit number.'
                ])
            ]
        ]);

        $builder->add('picture', new S3Photo($this->s3, '1rex/users.avatar'));

        $builder->add('mobile', 'text', [
            'constraints' => [
                new Assert\Length([
                    'max' => 100,
                    'maxMessage' => 'Mobile must be shorter than {{ limit }} chars.',
                ]),
                new Assert\Regex([
                    'pattern' => '/^[0-9+\(\)#\.\s\/ext-]+$/',
                    'message' => 'Please input a valid US mobile phone number.'
                ])
            ]
        ]);

        $builder->add('email', 'text', [
            'constraints' => [
                new Assert\NotBlank(['message' => 'Email should not be blank.']),
                new Assert\Email(),
                new Unique([
                    'groups' => ['New'],
                    'field'  => 'email',
                    'entity' => 'LO\\Model\\Entity\\User',
                    'notUniqueMessage' => 'Email address is already registered.'
                ]),
            ]
        ]);

        $builder->add('nmls', 'text', [
            'constraints' => [
                new Assert\Type(['type' => 'numeric', 'message' => 'NMLS # should be of type {{ type }}.']),
            ]
        ]);

        $builder->add('address', new AddressType(), [
            'cascade_validation' => true
        ]);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver){
        $resolver->setDefaults([
            'data_class' => User::class,
            'csrf_protection' => false,
            'allow_extra_fields' => true,
            'validation_groups' => ['Default']
        ]);
    }
}