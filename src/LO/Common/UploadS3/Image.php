<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 4/13/15
 * Time: 3:30 PM
 */

namespace LO\Common\UploadS3;

use LO\Exception\Http;
use Symfony\Component\HttpFoundation\Response;
use Aws\S3\S3Client;

class Image extends Base{
    private $img;
    private $ext;

    public function __construct(S3Client $s3, $blobImage, $bucket){
        parent::__construct($s3, $bucket);
        if(empty($blobImage)){
            throw new Http('Image content is empty.', Response::HTTP_BAD_REQUEST);
        }

        $this->img    = $this->createImage($blobImage);
    }

    public function downloadPhotoToS3andGetUrl($filename){
        return parent::downloadFileToS3andGetUrl($filename.'.'.$this->ext);
    }

    protected function createImage($image){
        try{
            $im = new \Imagick();

            $im->readimageblob(
                file_get_contents(
                    str_replace(' ','+',$image)
                )
            );
            $this->ext = $im->getImageFormat();

            return $im->getimageblob();
        }
        catch (\Exception $e) {
            throw new Http('This type is not supported.', Response::HTTP_BAD_REQUEST);

        }
        finally{
            $im->destroy();
        }
    }

    protected function getFile(){
        return $this->img;
    }

    protected function getContentType(){
        $contentType = ['jpg' => 'jpeg', 'png' => 'png', 'gif' => 'gif', 'tiff' => 'tiff', 'bmp' => 'bmp'];

        return 'image/'.(isset($contentType[$this->ext])? $contentType[$this->ext]: 'jpeg');
    }


}