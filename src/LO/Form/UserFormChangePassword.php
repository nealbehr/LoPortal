<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 6/4/15
 * Time: 11:48 AM
 */

namespace LO\Form;

use Aws\S3\S3Client;
use LO\Application;
use LO\Form\Transformer\PasswordTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class UserFormChangePassword extends UserFormType {
    private $app;

    public function __construct(Application $app, S3Client $s3){
        parent::__construct($s3);
        $this->app = $app;
    }

    public function buildForm(FormBuilderInterface $builder, array $options) {
        parent::buildForm($builder, $options);

        $transformer = new PasswordTransformer($this->app, $options['data']);

        $builder->add(
            $builder->create('password', 'repeated', array(
                'type' => 'password',
                'invalid_message' => 'The password fields must match.',
                'first_name' => 'password',
                'second_name' => 'password_confirm',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Password cant\'t be null.']),
                ],
            ))
            ->addModelTransformer($transformer)
        );
    }
} 