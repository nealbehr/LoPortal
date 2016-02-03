<?php
/**
 * Created by IntelliJ IDEA.
 * User: Dmitry K.
 * Date: 5/18/15
 * Time: 14:05
 */

namespace LO\Form;

use Aws\S3\S3Client;
use LO\Form\Extension\S3Photo;
use LO\Model\Entity\Lender;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Doctrine\ORM\EntityManager;

class LenderType extends AbstractType
{
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
        return 'lender';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text', [
            'constraints' => [
                new Assert\NotBlank([
                    'message' => 'Lender name can not be empty.'
                ]),
                new Assert\Length([
                    'max' => 50,
                    'maxMessage' => 'Lender name must be shorter than {{ limit }} chars.',
                ]),
                new Assert\Callback(function($param, ExecutionContextInterface $context) use ($options) {
                    if (isset($options['method'])
                        && 'POST' === strtoupper($options['method'])
                        && !empty($this->em->getRepository(Lender::class)->findOneBy(['name' => $param]))) {
                        $context->addViolation('Lender name already exists.');
                    }
                })
            ]
        ]);

        $builder->add('picture', new S3Photo($this->s3, '1rex/lenders.pictures'));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver){
        $resolver->setDefaults([
            'data_class' => Lender::class,
            'csrf_protection' => false,
            'allow_extra_fields' => true,
            'validation_groups' => ['Default'],
        ]);
    }
}