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
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class QueueForm extends AbstractType {
    public function getName() {
        return 'property';
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
            ->add('address', 'text')
            ->add('mls_number', 'text');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver){
        $resolver->setDefaults([
            'data_class'        => Queue::class,
            'csrf_protection'   => false,
            'validation_groups' => ['Default'],
        ]);
    }
}