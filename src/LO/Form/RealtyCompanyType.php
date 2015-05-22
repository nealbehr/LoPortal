<?php
/**
 * Created by IntelliJ IDEA.
 * User: mac
 * Date: 5/22/15
 * Time: 12:15
 */

namespace LO\Form;

use Aws\S3\S3Client;
use LO\Form\Extension\S3Photo;
use LO\Model\Entity\RealtyCompany;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

class RealtyCompanyType extends AbstractType {

    /**
     * @var \Aws\S3\S3Client $s3
     */
    private $s3;

    public function __construct(S3Client $s3){
        $this->s3 = $s3;
    }

    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'realtyCompany';
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('name', 'text', [
            'constraints' => [
                new Assert\NotBlank([
                    'message' => 'Name can not be empty.'
                ]),
                new Assert\Length([
                    'max' => 50,
                    'maxMessage' => 'Name must be shorter than {{ limit }} chars.',
                ])
            ]
        ]);

        $builder->add('logo', new S3Photo($this->s3, '1rex.realty.logo'));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver){
        $resolver->setDefaults([
            'data_class' => RealtyCompany::class,
            'csrf_protection' => false,
            'validation_groups' => ['Default'],
        ]);
    }
} 