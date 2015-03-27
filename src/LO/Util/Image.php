<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 3/26/15
 * Time: 11:49 AM
 */

namespace LO\Util;


use Aws\S3\Enum\CannedAcl;
use LO\Application;
use LO\Exception\Http;
use Symfony\Component\HttpFoundation\Response;

class Image {
    private $app;
    private $img;
    private $ext;
    private $bucket;

    public function __construct(Application $app, $blobImage, $bucket){
        if(empty($blobImage)){
            throw new Http('Image content is empty.', Response::HTTP_BAD_REQUEST);
        }

        $this->app    = $app;
        $this->bucket = $bucket;
        $this->img    = $this->createImage($blobImage);
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
        }finally{
            $im->destroy();
        }
    }

    public function downloadPhotoToS3andGetUrl($filename){
        $ext = null;

        /**
         * @var \Guzzle\Service\Resource\Model $answer
         */
        $response = $this->app->getS3()->putObject([
            'Bucket' => $this->bucket,
            'Key'    => $filename.'.'.$this->ext,
            'Body'   => $this->img,
            'ACL'         => CannedAcl::PUBLIC_READ,
            'ContentType' => $this->getContentType(),
        ]);

        return $response->get('ObjectURL');
    }

    private function getContentType(){
        $contentType = ['jpg' => 'jpeg', 'png' => 'png', 'gif' => 'gif', 'tiff' => 'tiff', 'bmp' => 'bmp'];

        return 'image/'.(isset($contentType[$this->ext])? $contentType[$this->ext]: 'jpeg');
    }


} 