<?php
/**
 * User: Eugene Lysenko
 * Date: 12/22/15
 * Time: 12:31
 */
namespace LO\Form;

use LO\Form\Extension\S3Photo;
use LO\Model\Entity\Template;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Aws\S3\S3Client;

class TemplateType extends AbstractType
{
    private $s3;

    public function __construct(S3Client $s3)
    {
        $this->s3 = $s3;
    }

    public function getName()
    {
        return 'template';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('picture', new S3Photo($this->s3, '1rex/tamplate'), [
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Last name should not be blank.']),
                    new Assert\Length([
                        'max'        => 255,
                        'maxMessage' => 'Name must be shorter than {{ limit }} chars.',
                    ])
                ]
            ])
            ->add('name', 'text', [
               'constraints' => [
                    new Assert\NotBlank(['message' => 'Last name should not be blank.']),
                    new Assert\Length([
                        'max'        => 50,
                        'maxMessage' => 'Name must be shorter than {{ limit }} chars.',
                    ])
               ]
            ])->add('description', 'text');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class'         => Template::class,
            'csrf_protection'    => false,
            'allow_extra_fields' => true,
            'validation_groups'  => ['Default'],
        ]);
    }
}
