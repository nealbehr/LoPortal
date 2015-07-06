<?php namespace LO\Form;

use LO\Form\Extension\S3Photo;
use LO\Model\Entity\Realtor;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use LO\Validator\Unique;
use Aws\S3\S3Client;
use LO\Validator\UniqueEntityCustom;

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

        /*$a = new UniqueEntity([
            'fields' => ['last_name', 'first_name'],
            //'errorPath'=> 'page',
            //'groups'=> ['New'],
            'message'=> "Page already exists with that parent",
            //'ignoreNull'=> false
        ]);

        $b = new Unique([
            'groups'           => ['New'],
            'field'            => 'last_name',
            'entity'           => 'LO\\Model\\Entity\\Realtor',
            'notUniqueMessage' => 'Realtor with the first name and last name is already registered.'
        ]);*/

        $builder->add('last_name', 'text', [
               'constraints' => [
                    new Assert\Regex([
                        'pattern' => "/^([A-Za-z-_\s]+)$/",
                        'message' => 'Name is invalid.'
                    ]),
                    new Assert\Length([
                        'max'        => 50,
                        'maxMessage' => 'Name must be shorter than {{ limit }} chars.',
                    ]),

            /*       new UniqueEntity([
  'fields' => ['last_name', 'first_name'],
  //'errorPath'=> 'page',
  //'groups'=> ['New'],
  'message'=> "Page already exists with that parent",
  //'ignoreNull'=> false
    ])*/
                    /*new Unique([
                        'groups'           => ['New'],
                        'field'            => 'last_name',
                        'entity'           => 'LO\\Model\\Entity\\Realtor',
                        'notUniqueMessage' => 'Realtor with the first name and last name is already registered.'
                    ])*/
               ]
            ])->add('first_name', 'text', [
                'constraints' => [
                    new Assert\Regex([
                        'pattern' => "/^([A-Za-z-_\s]+)$/",
                        'message' => 'Name is invalid.'
                    ]),
                    new Assert\Length([
                        'max'        => 50,
                        'maxMessage' => 'Name must be shorter than {{ limit }} chars.',
                    ]),
                    /*new Unique([
                        'groups'           => ['New'],
                        'field'            => 'first_name',
                        'entity'           => 'LO\\Model\\Entity\\Realtor',
                        'notUniqueMessage' => 'Realtor with the first name and last name is already registered.'
                    ])*/
                ]
            ])
            ->add('realty_company_id', 'number', [
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Realty company should not be blank.']),
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
            ])->add('bre_number', 'text');
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
