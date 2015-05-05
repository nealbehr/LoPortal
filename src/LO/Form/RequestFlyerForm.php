<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 4/8/15
 * Time: 7:43 PM
 */

namespace LO\Form;

use LO\Form\Extension\S3Photo;
use LO\Model\Entity\RequestFlyer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Aws\S3\S3Client;

class RequestFlyerForm extends AbstractType {
    private $s3;

    public function __construct(S3Client $s3){
        $this->s3 = $s3;
    }

    public function getName() {
        return 'property';
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('pdf_link', 'text')
            ->add('listing_price', 'text')
            ->add('photo', new S3Photo($this->s3, '1rex.property'));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver){
        $resolver->setDefaults([
            'data_class'        => RequestFlyer::class,
            'csrf_protection'   => false,
            'validation_groups' => ['Default'],
        ]);
    }
}