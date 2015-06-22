<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 5/14/15
 * Time: 4:43 PM
 */

namespace LO\Controller\Admin;

use LO\Application;
use LO\Exception\Http;
use LO\Form\FirstRexAddress;
use Symfony\Component\HttpFoundation\Request;
use LO\Controller\RequestFlyerBase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class AdminRequestFlyerController extends RequestFlyerBase {

    public function updateAction(Application $app, Request $request, $id){
        try {
            $app->getEntityManager()->beginTransaction();

            $queue = $this->getQueueById($app, $id);

            $formOptions = [
                'validation_groups' => ["Default", "main"],
                'method' => 'PUT'
            ];
            $firstRexForm = $app->getFormFactory()->create(new FirstRexAddress(), null, ['method' => 'PUT']);
            $firstRexForm->handleRequest($request);

            if(!$firstRexForm->isValid()){
                throw new BadRequestHttpException('Additional info is not valid');
            }

            $queue->setAdditionalInfo($firstRexForm->getData());

            $this->saveFlyer(
                $app,
                $request,
                $queue->getRealtor(),
                $queue,
                $formOptions
            );

            $app->getEntityManager()->commit();
        }catch (\Exception $e){
            $app->getEntityManager()->rollback();
            $app->getMonolog()->addError($e);
            $this->getMessage()->replace('message', $e instanceof Http? $e->getMessage(): 'We have some problems. Please try later.');
            return $app->json($this->getMessage()->get(), $e instanceof Http? $e->getStatusCode(): Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $app->json("success");
    }
} 