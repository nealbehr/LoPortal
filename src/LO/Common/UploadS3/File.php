<?php
/**
 * Created by PhpStorm.
 * User: eugene
 * Date: 1/28/16
 * Time: 11:39
 */
namespace LO\Common\UploadS3;

use \LO\Exception\Http,
    Symfony\Component\HttpFoundation\Response,
    Aws\S3\S3Client,
    \Imagick;

class File extends Base
{
    private $allowedContentType = [
        'application/pdf' => 'pdf',
        'image/jpeg'      => 'jpg',
        'image/png'       => 'png',
        'image/gif'       => 'gif',
        'image/bmp'       => 'bmp'
    ];

    private $file, $format, $content_type;

    /**
     * @param S3Client $s3
     * @param $base64String
     * @param $bucket
     * @throws Http
     */
    public function __construct(S3Client $s3, $base64String, $bucket)
    {
        parent::__construct($s3, $bucket);
        if (empty($base64String)) {
            throw new Http('File content is empty.', Response::HTTP_BAD_REQUEST);
        }

        $this->prepare($base64String);
    }

    /**
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * @param int $width
     * @param int $height
     * @param int $quality
     * @return $this
     */
    public function createPreview($width = 400, $height = 400, $quality = 80)
    {
        try{
            $im = new Imagick();
            $im->readImageBlob($this->file);
            $im->setImageFormat('jpeg');
            $im->setImageCompression(Imagick::COMPRESSION_JPEG);
            $im->setImageCompressionQuality($quality);
            $im->cropThumbnailImage($width, $height);

            $this->format       = $im->getImageFormat();
            $this->content_type = $im->getImageType();
            $this->file         = $im->getimageblob();

        } finally {
            $im->destroy();
        }

        return $this;
    }

    /**
     * @param $filename
     * @return mixed|null
     */
    public function download($filename)
    {
        return parent::downloadFileToS3andGetUrl($filename.'.'.$this->format);
    }

    /**
     * @param string $base64String
     * @return $this
     * @throws Http
     */
    protected function prepare($base64String)
    {
        $this->file         = file_get_contents(str_replace(' ', '+', $base64String));
        $this->content_type = strtolower((new \finfo(FILEINFO_MIME_TYPE))->buffer($this->file));
        if (!isset($this->allowedContentType[$this->content_type])) {
            throw new Http(sprintf("Content type '%s' not allowed.", $this->content_type), Response::HTTP_BAD_REQUEST);
        }
        $this->format       = $this->allowedContentType[$this->content_type];

        return $this;
    }

    /**
     * @return mixed
     */
    protected function getFile()
    {
        return $this->file;
    }

    /**
     * @return mixed
     */
    protected function getContentType()
    {
        return $this->content_type;
    }
}
