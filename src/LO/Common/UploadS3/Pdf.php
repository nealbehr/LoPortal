<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 4/13/15
 * Time: 3:44 PM
 */

namespace LO\Common\UploadS3;

use Aws\S3\S3Client;
use LO\Exception\Http;

class Pdf extends Base{
    private $fileContent;

    public function __construct(S3Client $s3, $blob, $bucket){
        parent::__construct($s3, $bucket);
        if(empty($blob)){
            throw new Http('Blob content is empty.', Response::HTTP_BAD_REQUEST);
        }

        $this->fileContent = $this->getContent($blob);
    }

    protected function getContent($blob){
        return file_get_contents(
            str_replace(' ', '+', $blob)
        );
    }

    protected function getFile(){
        return $this->fileContent;
    }

    protected function getContentType(){
        return "application/pdf";
    }
}