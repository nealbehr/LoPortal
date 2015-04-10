<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 4/10/15
 * Time: 8:03 PM
 */

namespace LO\Common\Email;

use Aws\Ses\SesClient;


abstract class Base {
    private $ses;
    private $source;
    private $destinationList = [];

    public function __construct(SesClient $ses, $source){
        $this->ses    = $ses;
        $this->source = $source;
    }

    abstract protected function getSubject();
    abstract protected function getBody();

    protected function getDestinationList(){
        return $this->destinationList;
    }

    public function setDestinationList($param){
        $this->destinationList = (array)$param;

        return $this;
    }

    protected function getSource(){
        return $this->source;
    }

    public function send(){
        if(empty($this->getDestinationList())){
            throw new \Exception('Destination must be not empty.');
        }

        $this->ses->sendEmail(
            [
                // Source is required
                'Source' => $this->getSource(),
                // Destination is required
                'Destination' => [
                    'ToAddresses' => $this->getDestinationList(),
                ],
                // Message is required
                'Message' => [
                    // Subject is required
                    'Subject' => [
                        // Data is required
                        'Data' => $this->getSubject(),
                        'Charset' => 'UTF8',
                    ],
                    // Body is required
                    'Body' => [
//                        'Text' => [
//                            // Data is required
//                            'Data' => 'stringBody',
//                            'Charset' => 'UTF8',
//                        ],
                        'Html' => [
                            // Data is required
                            'Data' => $this->getBody(),
                            'Charset' => 'UTF8',
                        ],
                    ],
                ],
            ]
        );
    }
} 