<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 3/25/15
 * Time: 12:36 PM
 */

namespace LO\Controller;


use LO\Application;
use LO\Model\Entity\Queue;
use LO\Model\Entity\Realtor;
use Symfony\Component\HttpFoundation\Request;
use Curl\Curl;

class LoRequest {
    public function addAction(Application $app, Request $request){
        try {
            $app->getEntityManager()->beginTransaction();

//            $id = $this->sendRequestTo1Rex($app,
//                array_merge(
//                    $request->get('address'),
//                    [
//                        'inquiry_type' => 'Seller of home',
//                        'product_type' => 'HB',
//                        'inquirer' => 'mcoudsi',
//                        'agent_name' => (string)$app->user()
//                    ]
//                )
//            );

            $realtor = (new Realtor())->fillFromArray($request->get('realtor', []));

            $app->getEntityManager()->persist($realtor);
            $app->getEntityManager()->flush();

            $request = (new Queue())->fillFromArray($request->get('property', []))
                                    ->set1RexId($realtor->getId())

            ;

            $app->getEntityManager()->commit();
        }catch (\Exception $e){
            $app->getEntityManager()->rollback();
            throw $e;
        }

        return $app->json("success");
    }


    protected function sendRequestTo1Rex(Application $app, array $data){
        try{
            $curl = new Curl();
            $curl->setBasicAuthentication($app->getConfigByName('firstrex', 'api', 'user'), $app->getConfigByName('firstrex', 'api', 'pass'));
            $curl->setHeader('Content-Type', 'application/json');
            $curl->post($app->getConfigByName('firstrex', 'api', 'url'), json_encode($data));

            if ($curl->error) {
                throw new \Exception(sprintf('Curl error: \'%d\': \'%s\'', $curl->error_code, $curl->error_message));
            }

            $result = json_decode($curl->response, true);

            if(false === $result || !isset($result['id'])){
                throw new \Exception(sprintf('Bad response have taken from 1REX. Response \'%s\'', $curl->response));
            }

            return $result['id'];
        }catch (\Exception $e){
            throw $e;
        }finally{
            $curl->close();
        }
    }
} 