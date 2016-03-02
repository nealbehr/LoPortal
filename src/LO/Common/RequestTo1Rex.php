<?php
/**
 * User: Eugene Lysenko
 * Date: 12/11/15
 * Time: 11:14
 */
namespace LO\Common;

use Curl\Curl;
use LO\Application;
use LO\Model\Entity\User;
use LO\Model\Entity\Queue;

class RequestTo1Rex
{
    const PRODUCT_TYPE       = 'HB';
    const BILLBOARD_SOURCE   = 'LO Portal';

    const TYPE_PREQUAL       = 0;
    const TYPE_LISTING_FLYER = 1;

    private $inQuiryText     = [
        Queue::TYPE_USER_SELLER => 'Seller of home',
        Queue::TYPE_USER_BUYER  => 'Buyer of home'
    ];

    private $requestTypeText = [
        self::TYPE_PREQUAL       => 'Prequal',
        self::TYPE_LISTING_FLYER => 'Listing Flyer'
    ];

    protected $data          = [];

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * @param $param
     * @return $this
     */
    public function setType($param)
    {
        $this->data = array_merge($this->data, [
            'request_type' => $this->requestTypeText[$param]
        ]);

        return $this;
    }

    public function setAddress(array $address)
    {
        $this->data = array_merge($this->data, $address);

        return $this;
    }

    public function setUser(User $user)
    {
        $this->data = array_merge(
            $this->data,
            [
                'inquirer'       => $user->getSalesDirector(),
                'inquirer_email' => $user->getSalesDirectorEmail(),
                'agent_name'     => sprintf('%s %s', $user->getFirstName(), $user->getLastName())
            ]
        );

        return $this;
    }

    public function setQueue(Queue $queue)
    {
        $this->data = array_merge($this->data, [
            'apt'          => $queue->getApartment(),
            'external_id'  => $queue->getId(),
            'inquiry_type' => $this->inQuiryText[$queue->getUserType()]
        ]);

        return $this;
    }

    public function send()
    {
        return $this->sendRequestTo1Rex();
    }

    protected function sendRequestTo1Rex()
    {
        $id1Rex = 0;
        $curl   = new Curl();
        try {
            $curl->setBasicAuthentication(
                $this->app->getConfigByName('firstrex', 'api', 'user'),
                $this->app->getConfigByName('firstrex', 'api', 'pass')
            );
            $curl->setHeader('Content-Type', 'application/json');
            $curl->post($this->app->getConfigByName('firstrex', 'api', 'url'), json_encode($this->getData()));

            if ($curl->error) {
                throw new \Exception(
                    sprintf(
                        'Curl error: \'%d\': \'%s\'. Response: %s',
                        $curl->error_code,
                        $curl->error_message,
                        print_r($curl->response, true)
                    )
                );
            }

            if (false === $curl->response || !property_exists($curl->response, 'id')) {
                throw new \Exception(sprintf('Bad response have taken from 1REX. Response \'%s\'', $curl->response));
            }

            $id1Rex = $curl->response->id;
        }
        catch (\Exception $e) {
            $this->app->getMonolog()->addError($e);
            throw $e;
        }
        finally {
            $curl->close();
        }

        return $id1Rex;
    }

    private function getData()
    {
        return array_merge(
            $this->data,
            // Default data
            [
                'product_type' => self::PRODUCT_TYPE,
                'source'       => self::BILLBOARD_SOURCE,
            ]
        );
    }
}
