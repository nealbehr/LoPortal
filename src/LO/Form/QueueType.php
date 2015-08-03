<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 4/8/15
 * Time: 7:31 PM
 */

namespace LO\Form;

use LO\Model\Entity\Queue;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use LO\Form\Extension\S3Photo;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Aws\S3\S3Client;

class QueueType extends AbstractType {

    private $s3;

    public function __construct(S3Client $s3){
        $this->s3 = $s3;
    }

    public function getName() {
        return 'property';
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('address', 'text')
            ->add('apartment', 'text')
            ->add('mls_number', 'text')

            ->add('listing_price', 'number', [
                    'precision' => 0,
                    'empty_data' => '0',
                ]
            )
            ->add('omit_realtor_info', 'number', [
                'precision'  => 0,
                'empty_data' => '1'
            ])
            ->add('funded_percentage', 'percent', array(
                'precision' => 2,
                'type' => 'integer',
                'empty_data' => '10'
            ))
            ->add('maximum_loan', 'percent', array(
                'precision' => 2,
                'type' => 'integer',
                'empty_data' => '80'
            ))
            ->add('photo', new S3Photo($this->s3, '1rex/property'));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver){
        $resolver->setDefaults([
            'data_class'        => Queue::class,
            'csrf_protection'   => false,
            'allow_extra_fields' => true,
            'validation_groups' => ['Default'],
        ]);
    }
}