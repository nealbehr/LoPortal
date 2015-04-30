<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 4/8/15
 * Time: 5:41 PM
 */

namespace LO\Controller;

use Curl\Curl;
use LO\Application;
use LO\Model\Entity\User;

class RequestBaseController {
    protected function sendRequestTo1Rex(Application $app, array $address, User $user){
        try{
            $data = array_merge(
                $address,
                [
                    'inquiry_type' => 'Seller of home',
                    'product_type' => 'HB',
                    'inquirer'     => $user->getSalesDirector(),
//                    'agent_name'   => (string)$user,
                    'agent'        => (string)$user,
                ]
            );

            $curl = new Curl();
            $curl->setBasicAuthentication($app->getConfigByName('firstrex', 'api', 'user'), $app->getConfigByName('firstrex', 'api', 'pass'));
            $curl->setHeader('Content-Type', 'application/json');
            $curl->post($app->getConfigByName('firstrex', 'api', 'url'), json_encode($data));

            if ($curl->error) {
                throw new \Exception(sprintf('Curl error: \'%d\': \'%s\'', $curl->error_code, $curl->error_message));
            }

            if(false === $curl->response || !property_exists($curl->response, 'id')){
                throw new \Exception(sprintf('Bad response have taken from 1REX. Response \'%s\'', $curl->response));
            }

            return $curl->response->id;
        }catch (\Exception $e){
            throw $e;
        }finally{
            $curl->close();
        }
    }

} 