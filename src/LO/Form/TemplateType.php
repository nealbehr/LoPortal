<?php
/**
 * User: Eugene Lysenko
 * Date: 12/22/15
 * Time: 12:31
 */

namespace LO\Form;

use LO\Model\Entity\Template,
    Symfony\Component\Form\AbstractType,
    Symfony\Component\Form\FormBuilderInterface,
    Symfony\Component\Validator\Constraints as Assert,
    Symfony\Component\OptionsResolver\OptionsResolverInterface,
    Aws\S3\S3Client;

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
            ->add('archive', 'number', [
                'precision'  => 0,
                'empty_data' => '0'
            ])
            ->add('co_branded', 'number', [
                'precision'  => 0,
                'empty_data' => '1'
            ])
            ->add('lenders_all', 'number', [
                'precision'  => 0,
                'empty_data' => '1'
            ])
            ->add('states_all', 'number', [
                'precision'  => 0,
                'empty_data' => '1'
            ])
            ->add('name', 'text', [
               'constraints' => [
                    new Assert\NotBlank(['message' => 'Name should not be blank.']),
                    new Assert\Length([
                        'max'        => 50,
                        'maxMessage' => 'Name must be shorter than {{ limit }} chars.',
                    ])
               ]
            ])
            ->add('description', 'text');
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
