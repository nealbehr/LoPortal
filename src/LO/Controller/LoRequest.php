<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 3/25/15
 * Time: 12:36 PM
 */

namespace LO\Controller;


use LO\Application;
use LO\Exception\Http;
use LO\Model\Entity\Queue;
use LO\Model\Entity\Realtor;
use LO\Util\Image;
use Symfony\Component\HttpFoundation\Request;
use Curl\Curl;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolation;

class LoRequest {
    public function addAction(Application $app, Request $request){
        try {
            $data = [];
            $app->getEntityManager()->beginTransaction();

            $id = $this->sendRequestTo1Rex($app,
                array_merge(
                    $request->get('address'),
                    [
                        'inquiry_type' => 'Seller of home',
                        'product_type' => 'HB',
                        'inquirer' => 'mcoudsi',
                        'agent_name' => (string)$app->user()
                    ]
                )
            );

            $helper = new Image($app, $request->get('realtor')['image'], '1rex.realtor');
            $photoUrl = $helper->downloadPhotoToS3andGetUrl(time().mt_rand(1, 100000));

            $realtor = (new Realtor())->fillFromArray($request->get('realtor', []))
                        ->setPhoto($photoUrl)
            ;

            $errors = $app->getValidator()->validate($realtor);
            if(count($errors)){
                $data['realtor'] = [];
                /** @var ConstraintViolation $error */
                foreach($errors as $error){
                    $data['realtor'][] = [$error->getPropertyPath() => $error->getMessage()];
                }

                throw new Http('Realtor info is not valid', Response::HTTP_BAD_REQUEST);
            }

            $app->getEntityManager()->persist($realtor);
            $app->getEntityManager()->flush();

            $queue = (new Queue())->fillFromArray($request->get('property', []))
                                    ->set1RexId($id)
                                    ->setRealtorId($realtor->getId())
                                    ->setType(Queue::TYPE_FLYER)

            ;

            $helper = new Image($app, $request->get('property')['image'], '1rex.property');
            $photoUrl = $helper->downloadPhotoToS3andGetUrl(time().mt_rand(1, 100000));

            $queue->setPhoto($photoUrl);

            $errors = $app->getValidator()->validate($queue);
            if(count($errors)){
                $data['property'] = [];
                /** @var ConstraintViolation $error */
                foreach($errors as $error){
                    $data['property'][] = [$error->getPropertyPath() => $error->getMessage()];
                }

                throw new Http('Property info is not valid', Response::HTTP_BAD_REQUEST);
            }

            $app->getEntityManager()->persist($queue);
            $app->getEntityManager()->flush();

            $app->getEntityManager()->commit();
        }catch (\Exception $e){
            $app->getEntityManager()->rollback();
            $app->getMonolog()->addError($e);
            $data['message'] = $e instanceof Http? $e->getMessage(): 'We have some problems. Please try later.';
            return $app->json($data, $e instanceof Http? $e->getStatusCode(): Response::HTTP_INTERNAL_SERVER_ERROR);
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