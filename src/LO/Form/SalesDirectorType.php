<?php namespace LO\Form;

use Aws\S3\S3Client;
use LO\Model\Entity\SalesDirector;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Doctrine\ORM\EntityManager;

class SalesDirectorType extends AbstractType
{
    /**
     * @var \Aws\S3\S3Client $s3
     */
    private $em, $s3;

    public function __construct(EntityManager $em, S3Client $s3)
    {
        $this->em = $em;
        $this->s3 = $s3;
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'salesDirector';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text', [
            'constraints' => [
                new Assert\Regex([
                    'pattern' => "/^([A-Za-z-_\s]+)$/",
                    'message' => 'Name is invalid.'
                ]),
                new Assert\Length([
                    'max'        => 255,
                    'maxMessage' => 'Name must be shorter than {{ limit }} chars.',
                ])
            ]
        ])->add('email', 'text', [
            'constraints' => [
                new Assert\NotBlank(['message' => 'Email should not be blank.']),
                new Assert\Email(),
                new Assert\Callback(function($param, ExecutionContextInterface $context) use ($options) {
                    if (isset($options['method'])
                        && 'POST' === strtoupper($options['method'])
                        && !empty($this->em->getRepository(SalesDirector::class)->findOneBy(['email' => $param]))) {
                        $context->addViolation('Email address is already registered.');
                    }
                })
            ]
        ])->add('phone', 'text', [
            'constraints' => [
                new Assert\Length([
                    'max'        => 100,
                    'maxMessage' => 'Sales director phone must be shorter than {{ limit }} chars.',
                ]),
                new Assert\Regex([
                    'pattern' => '/^[0-9+\(\)#\.\s\/ext-]+$/',
                    'message' => 'Please input a valid US phone number including 3 digit area code and 7 digit number.'
                ])
            ]
        ]);
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class'         => SalesDirector::class,
            'csrf_protection'    => false,
            'allow_extra_fields' => true,
            'validation_groups'  => ['Default'],
        ]);
    }
}
