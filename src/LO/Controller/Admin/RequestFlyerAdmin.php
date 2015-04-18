<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 4/17/15
 * Time: 10:27 AM
 */

namespace LO\Controller\Admin;


use LO\Application;
use LO\Exception\Http;
use LO\Form\QueueForm;
use LO\Form\RealtorForm;
use LO\Form\RequestFlyerForm;
use LO\Model\Entity\Queue;
use LO\Model\Entity\Realtor;
use LO\Model\Manager\QueueManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use LO\Traits\GetFormErrors;
use LO\Controller\RequestBaseController;

class RequestFlyerAdmin extends RequestBaseController{
    use GetFormErrors;

    public function getAction(Application $app, $id){
        /** @var Queue $queue */
        $queue = (new QueueManager($app))->getByIdWithRequestFlyeAndrUser($id);

        if(!$queue){
            throw new Http(sprintf('Request \'%s\' not found.', $id), Response::HTTP_BAD_REQUEST);
        }

        $queueForm = $app->getFormFactory()->create(new QueueForm());

        $requestFlyer = $queue->getFlyer();

        $formFlyerForm = $app->getFormFactory()->create(new RequestFlyerForm($app->getS3()));

        $realtor = $app->getEntityManager()->getRepository(Realtor::class)->find($requestFlyer->getRealtorId());
        $realtorForm = $app->getFormFactory()->create(new RealtorForm($app->getS3()));

        return $app->json([
            'property' => array_merge($this->removeExtraFields($queue->toArray(), $queueForm), $this->removeExtraFields($requestFlyer->toArray(), $formFlyerForm)),
            'realtor'  => $this->removeExtraFields($realtor->toArray(), $realtorForm),
            'user'     => $queue->getUser()->getPublicInfo(),
        ]);
    }

    public function updateAction(Application $app, Request $request, $id){
        try {
            $app->getEntityManager()->beginTransaction();
            $data = [];

            /** @var Queue $queue */
            $queue = (new QueueManager($app))->getByIdWithRequestFlyeAndrUser($id);

            if(!$queue){
                throw new Http(sprintf("Request flyer \'%s\' not found.", $id), Response::HTTP_BAD_REQUEST);
            }

            /** @var Realtor $realtor */
            $realtor = $app->getEntityManager()
                ->getRepository(Realtor::class)
                ->find($queue->getFlyer()->getRealtorId());

            $id = $this->sendRequestTo1Rex($app, $request->get('address'), (string)$queue->getUser());

            $queue->set1RexId($id);

            $form = $app->getFormFactory()->create(new RealtorForm($app->getS3()), $realtor, ['method' => 'PUT']);
            $form->handleRequest($request);
            if(!$form->isValid()){
                $data = array_merge($data, ['realtor' => $this->getFormErrors($form)]);

                throw new Http('Realtor info is not valid', Response::HTTP_BAD_REQUEST);
            }

            $app->getEntityManager()->persist($realtor);

            $queueForm = $app->getFormFactory()->create(new QueueForm(), $queue);
            $queueForm->submit($this->removeExtraFields($request->request->get('property'), $queueForm));

            if(!$queueForm->isValid()){
                $data = array_merge($data, ['property' => $this->getFormErrors($queueForm)]);

                throw new Http('Property info is not valid', Response::HTTP_BAD_REQUEST);
            }

            $app->getEntityManager()->persist($queue);

            $formRequestFlyer = $app->getFormFactory()->create(new RequestFlyerForm($app->getS3()), $queue->getFlyer());
            $formRequestFlyer->submit($this->removeExtraFields($request->request->get('property'), $formRequestFlyer));

            if(!$formRequestFlyer->isValid()){
                $data = array_merge($data, ['property' => $this->getFormErrors($form)]);

                throw new Http('Property info is not valid', Response::HTTP_BAD_REQUEST);
            }

            $app->getEntityManager()->persist($queue->getFlyer());
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
} 