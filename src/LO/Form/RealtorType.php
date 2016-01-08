<?php namespace LO\Form;

use LO\Form\Extension\S3Photo;
use LO\Model\Entity\Realtor;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Aws\S3\S3Client;

class RealtorType extends AbstractType
{
    private $s3;

    public function __construct(S3Client $s3)
    {
        $this->s3 = $s3;
    }

    public function getName()
    {
        return 'realtor';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('last_name', 'text', [
               'constraints' => [
                    new Assert\NotBlank(['message' => 'Last name should not be blank.']),
                    new Assert\Regex([
                        'pattern' => "/^([A-Za-z-_\s]+)$/",
                        'message' => 'Name is invalid.'
                    ]),
                    new Assert\Length([
                        'max'        => 50,
                        'maxMessage' => 'Name must be shorter than {{ limit }} chars.',
                    ])
               ]
            ])
            ->add('first_name', 'text', [
                'constraints' => [
                    new Assert\NotBlank(['message' => 'First name should not be blank.']),
                    new Assert\Regex([
                        'pattern' => "/^([A-Za-z-_\s]+)$/",
                        'message' => 'Name is invalid.'
                    ]),
                    new Assert\Length([
                        'max'        => 50,
                        'maxMessage' => 'Name must be shorter than {{ limit }} chars.',
                    ])
                ]
            ])
            ->add('photo', new S3Photo($this->s3, '1rex/realtor'))
            ->add('email', 'text', [
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Email should not be blank.']),
                    new Assert\Email()
                ]
            ])
            ->add('phone', 'text', [
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Phone should not be blank.']),
                    new Assert\Length([
                        'max'        => 100,
                        'maxMessage' => 'Sales director phone must be shorter than {{ limit }} chars.',
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^[0-9+\(\)#\.\s\/ext-]+$/',
                        'message' => 'Please input a valid US phone number including 3 digit area code and 7 digit number.'
                    ])
                ]
            ])
            ->add('bre_number', 'text')
            ->add('realty_logo', new S3Photo($this->s3, '1rex-realty'))
            ->add('realty_name', 'text', [
                'constraints' => [
                    new Assert\Regex([
                        'pattern' => '/^[a-zA-Z0-9][a-zA-Z0-9()\.\-#&\s]*$/',
                        'message' => 'Name is invalid.'
                    ]),
                    new Assert\Length([
                        'max'        => 50,
                        'maxMessage' => 'Name must be shorter than {{ limit }} chars.',
                    ])
                ]
            ]);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class'         => Realtor::class,
            'csrf_protection'    => false,
            'allow_extra_fields' => true,
            'validation_groups'  => ['Default'],
        ]);
    }
}
