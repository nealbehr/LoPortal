<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 3/25/15
 * Time: 12:36 PM
 */

namespace LO\Controller;


use LO\Application;
use LO\Common\Email\Request\RequestChangeStatus;
use LO\Common\Email\Request\RequestFlyerSubmission;
use LO\Exception\Http;
use LO\Form\QueueForm;
use LO\Form\RealtorForm;
use LO\Form\RequestFlyerForm;
use LO\Model\Entity\Queue;
use LO\Model\Entity\Realtor;
use LO\Model\Entity\RequestFlyer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use LO\Traits\GetFormErrors;

class RequestFlyerController extends RequestBaseController{
    use GetFormErrors;

    public function addAction(Application $app, Request $request){
        try {
            $data = [];
            $app->getEntityManager()->beginTransaction();

            $id = $this->sendRequestTo1Rex($app, $request->get('address'), $app->user());
            $realtor = new Realtor();

            $form = $app->getFormFactory()->create(new RealtorForm($app->getS3()), $realtor);
            $form->handleRequest($request);
            if(!$form->isValid()){
                $data = array_merge($data, ['realtor' => $this->getFormErrors($form)]);

                throw new Http('Realtor info is not valid', Response::HTTP_BAD_REQUEST);
            }

            $app->getEntityManager()->persist($realtor);
            $app->getEntityManager()->flush();



            $queue = (new Queue())->set1RexId($id)
                                  ->setType(Queue::TYPE_FLYER)
                                  ->setUser($app->user())
            ;

            $queueForm = $app->getFormFactory()->create(new QueueForm(), $queue);
            $queueForm->submit($this->removeExtraFields($request->request->get('property'), $queueForm));

            if(!$queueForm->isValid()){
                $data = array_merge($data, ['property' => $this->getFormErrors($queueForm)]);

                throw new Http('Property info is not valid', Response::HTTP_BAD_REQUEST);
            }

            $app->getEntityManager()->persist($queue);
            $app->getEntityManager()->flush();

            $requestFlyer = (new RequestFlyer())
                ->setRealtorId($realtor->getId())
                ->setQueue($queue)
            ;

            $formRequestFlyer = $app->getFormFactory()->create(new RequestFlyerForm($app->getS3()), $requestFlyer);
            $formRequestFlyer->submit($this->removeExtraFields($request->request->get('property'), $formRequestFlyer));

            if(!$formRequestFlyer->isValid()){
                $data = array_merge($data, ['property' => $this->getFormErrors($form)]);

                throw new Http('Property info is not valid', Response::HTTP_BAD_REQUEST);
            }

            $app->getEntityManager()->persist($requestFlyer);
            $app->getEntityManager()->flush();

            (new RequestChangeStatus($app,  $queue, new RequestFlyerSubmission($realtor, $requestFlyer)))
                ->send();

            $app->getEntityManager()->commit();
        }catch (\Exception $e){
            $app->getEntityManager()->rollback();
            $app->getMonolog()->addError($e);
            $data['message'] = $e instanceof Http? $e->getMessage(): 'We have some problems. Please try later.';
            return $app->json($data, $e instanceof Http? $e->getStatusCode(): Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $app->json("success");
    }
}