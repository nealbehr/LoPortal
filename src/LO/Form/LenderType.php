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

class LenderType extends AbstractType {

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
        return 'lender';
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder->add('name', 'text', [
            'constraints' => [
                new Assert\NotBlank([
                    'message' => 'Lender name can not be empty.'
                ]),
                new Assert\Length([
                    'max' => 50,
                    'maxMessage' => 'Lender name must be shorter than {{ limit }} chars.',
                ])
            ]
        ]);

        $builder->add('picture', new S3Photo($this->s3, '1rex.lenders.pictures'));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver){
        $resolver->setDefaults([
            'data_class' => Lender::class,
            'csrf_protection' => false,
            'validation_groups' => ['Default'],
        ]);
    }
}