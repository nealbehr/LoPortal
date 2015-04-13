<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 4/13/15
 * Time: 3:29 PM
 */

namespace LO\Common\UploadS3;

use Aws\S3\S3Client;
use Aws\S3\Enum\CannedAcl;

abstract class Base {
    private $s3;
    private $bucket;

    public function __construct(S3Client $s3, $bucket){
        $this->s3     = $s3;
        $this->bucket = $bucket;
    }

    abstract protected function getFile();
    abstract protected function getContentType();

    public function downloadFileToS3andGetUrl($filename){
        /**
         * @var \Guzzle\Service\Resource\Model $answer
         */
        $response = $this->getS3()->putObject([
            'Bucket' => $this->getBucket(),
            'Key'    => $filename,
            'Body'   => $this->getFile(),
            'ACL'         => CannedAcl::PUBLIC_READ,
            'ContentType' => $this->getContentType(),
        ]);

        return $response->get('ObjectURL');
    }

    protected function getS3(){
        return $this->s3;
    }

    protected function getBucket(){
        return $this->bucket;
    }

} 