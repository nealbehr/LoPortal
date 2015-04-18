<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 2/2/15
 * Time: 10:33 AM
 */

namespace LO\Form\Extension;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormBuilderInterface;
use LO\Common\UploadS3\Image;
use Aws\S3\S3Client;

class S3Photo extends AbstractType{
    /** @var S3Client $s3 */
    private $s3;

    /** @var  String */
    private $bucket;

    public function __construct(S3Client $s3, $bucket){
        $this->s3     = $s3;
        $this->bucket = $bucket;
    }

    public function buildForm(FormBuilderInterface $builder, array $options){
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function(FormEvent $event) use ($builder){
            $event->setData($this->prepareData($event->getData()));
        });
    }

    private function prepareData($data){
        if(filter_var($data, FILTER_VALIDATE_URL) !== false){
            return $data;
        }

        $helper = new Image($this->s3, $data, $this->bucket);
        return $helper->downloadPhotoToS3andGetUrl(time().mt_rand(1, 100000));
    }

    public function getName(){
        return 's3Image';
    }

    public function getParent(){
        return 'text';
    }
} 