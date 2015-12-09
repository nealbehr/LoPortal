<?php namespace LO\Common;

use Curl\Curl;
use LO\Application;
use LO\Model\Entity\User;
use LO\Model\Entity\Queue;

class RequestTo1Rex
{
    const BILLBOARD_SOURCE = 'LO Portal';

    protected $data = [];

    private $inQuiryText = [
        Queue::TYPE_USER_SELLER => 'Seller of home',
        Queue::TYPE_USER_BUYER  => 'Buyer of home'
    ];

    public function __construct(Application $app)
    {
        $this->app = $app;
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
        $curl = new Curl();
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

            return $curl->response->id;
        }
        catch (\Exception $e) {
            $this->app->getMonolog()->addError($e);
            throw $e;
        }
        finally {
            $curl->close();
        }

        return 0;
    }

    private function getData()
    {
        return array_merge(
            $this->data,
            [
                'product_type' => 'HB',
                'source'       => self::BILLBOARD_SOURCE
            ]
        );
    }
}
