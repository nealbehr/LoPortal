<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 4/8/15
 * Time: 6:07 PM
 */

namespace LO\Form;

use LO\Form\Extension\S3Photo;
use LO\Model\Entity\Realtor;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Aws\S3\S3Client;

class RealtorForm extends AbstractType {
    private $s3;

    public function __construct(S3Client $s3){
        $this->s3 = $s3;
    }

    public function getName() {
        return 'realtor';
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('last_name', 'text')
            ->add('first_name', 'text')
            ->add('estate_agency', 'text')
            ->add('photo', new S3Photo($this->s3, '1rex.realtor'))
            ->add('email', 'text')
            ->add('phone', 'text')
            ->add('bre_number', 'text');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver){
        $resolver->setDefaults([
            'data_class'        => Realtor::class,
            'csrf_protection'   => false,
            'validation_groups' => ['Default'],
        ]);
    }
}