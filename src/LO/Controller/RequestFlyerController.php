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
use LO\Common\Email\Request\RequestFlyerApproval;
use LO\Common\Email\Request\RequestFlyerSubmission;
use LO\Exception\Http;
use LO\Form\FirstRexAddress;
use LO\Form\QueueForm;
use LO\Form\RealtorForm;
use LO\Form\RequestFlyerForm;
use LO\Model\Entity\Queue;
use LO\Model\Entity\Realtor;
use LO\Model\Entity\RequestFlyer;
use LO\Model\Entity\User;
use LO\Model\Manager\QueueManager;
use LO\Model\Manager\RequestFlyerManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Knp\Snappy\Pdf;


class RequestFlyerController extends RequestFlyerBase {

    public function getAction(Application $app, $id){
        $queue = $this->getQueueById($app, $id);

        $queueForm = $app->getFormFactory()->create(new QueueForm(), $queue);

        $requestFlyer = $queue->getFlyer();

        $formFlyerForm = $app->getFormFactory()->create(new RequestFlyerForm($app->getS3()), $requestFlyer);

        $realtor = $app->getEntityManager()->getRepository(Realtor::CLASS_NAME)->find($requestFlyer->getRealtorId());
        $realtorForm = $app->getFormFactory()->create(new RealtorForm($app->getS3()), $realtor);

        return $app->json([
            'property' => array_merge($this->getFormFieldsAsArray($queueForm), $this->getFormFieldsAsArray($formFlyerForm), ['state' => $queue->getState()]),
            'realtor'  => $this->getFormFieldsAsArray($realtorForm),
            'address'  => $queue->getAdditionalInfo(),
            'user'     => $queue->getUser()->getPublicInfo(),
        ]);
    }

    /**
     * Download request flyer in PDF
     * @param Application $app
     * @param $id
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function download(Application $app, $id) {
        $manager = new RequestFlyerManager($app);
        $flyer = $manager->getById($id);
        if($flyer) {

            try {
                $pdf = new Pdf();
                $pdf->setBinary('/usr/local/bin/wkhtmltopdf');
                $pdf->setOption('dpi', 300);
                $pdf->setOption('page-width', '8.5in');
                $pdf->setOption('page-height', '11in');
                $pdf->setOption('margin-left', 0);
                $pdf->setOption('margin-right', 0);
                $pdf->setOption('margin-top', 0);
                $pdf->setOption('margin-bottom', 0);

                $time = time();
                $pdfFile = 'flayer-' . $id . '-'. $time . '.pdf';
                $html = $app->getTwig()->render('request.flyer.pdf.twig', $this->getPDFData());

                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename="' . $pdfFile . '"');
                echo $pdf->getOutputFromHtml($html, array(), true);

            } catch (\Exception $ex) {
                header_remove('Content-Type');
                header_remove('Content-Disposition');
                return $app->json(['error' => '', 'message' => $ex->getMessage()]);
            }
        }
        return $app->json("Error. Flyer not found");
    }

    public function contentForPDF(Application $app, $id) {

        return $app->getTwig()->render('request.flyer.pdf.twig', $this->getPDFData());

    }

    private function getPDFData() {
        $data = array(
            'noteFont' => 'inherit', // oswaldlight or inherit

            'homeAddress' =>  '123 Easy Street <br> Pleasantville, CA 94110',
            'homeImage' => 'http://i.imgur.com/O68xKOh.png',
            'discuontPart' => '10',
            'discuont' => '100,000',
            'listingPrice' => '1,000,000',
            'availableLoan' => '800,000',
            'requiredDownPayment' => '200,000',
            'ourDownPayment' => '100,000',
            'yourDownPayment' => '100,000',

            'photoCard1' => 'http://i.imgur.com/Wyd2mtJ.png',
            'nameCard1' => 'Moe House',
            'infoCard1' => 'Sr. Loan Officer<br />
                Ph: 415.555.1212<br />
                mohouse@abclending.com<br />
                ABC Lending<br />
                555 Commercial Way<br />
                Anytown, CA 94939<br />
                NMLS #555555<br />
                CA BRE #01555555',
            'agencyCard1' => 'http://i58.tinypic.com/tag2ty.png',

            'photoCard2' => 'http://i.imgur.com/7W9wwAw.png',
            'nameCard2' => 'Shirly Jurgiokin',
            'infoCard2' => 'Realtor<sup>®</sup><br />
                415-555-1515<br />
                ShirlyJ@acmerealty.com<br />
                CA BRE #5555555',
            'agencyCard2' => 'http://i58.tinypic.com/1zyfoee.png'
        );
        return $data;
    }

    public function addAction(Application $app, Request $request){
        try {
            $formOptions = ['validation_groups' => ["Default", "main"]];
            $app->getEntityManager()->beginTransaction();

            $firstRexForm = $app->getFormFactory()->create(new FirstRexAddress());
            $firstRexForm->handleRequest($request);

            if(!$firstRexForm->isValid()){
//                $data = array_merge($data, ['address' => $this->getFormErrors($firstRexForm)]);

                throw new Http('Additional info is not valid', Response::HTTP_BAD_REQUEST);
            }

            $id = $this->sendRequestTo1Rex($app, $firstRexForm->getData(), $app->user());

            $realtor      = new Realtor();
            $requestFlyer = new RequestFlyer();
            $queue        = (new Queue())->set1RexId($id)->setAdditionalInfo($firstRexForm->getData());

            $this->saveFlyer(
                $app,
                $request,
                $realtor,
                $queue,
                $requestFlyer,
                $formOptions
            );

            (new RequestChangeStatus($app,  $queue, new RequestFlyerSubmission($realtor, $requestFlyer)))->send();

            $app->getEntityManager()->commit();
        }catch (\Exception $e){
            $app->getEntityManager()->rollback();
            $app->getMonolog()->addError($e);
            $this->getMessage()->replace('message', $e instanceof Http? $e->getMessage(): 'We have some problems. Please try later.');
            return $app->json($this->getMessage()->get(), $e instanceof Http? $e->getStatusCode(): Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $app->json("success");
    }

    public function updateAction(Application $app, Request $request, $id){
        try {
            $app->getEntityManager()->beginTransaction();

            $queue = $this->getQueueById($app, $id);

            if($queue->getState() !== Queue::STATE_DRAFT){
                throw new Http("You can't edit this flyer.", Response::HTTP_BAD_REQUEST);
            }

            $queue->setState(Queue::STATE_REQUESTED);

            $this->update($app, $request, $queue);

            $app->getEntityManager()->commit();
        }catch (\Exception $e){
            $app->getEntityManager()->rollback();
            $app->getMonolog()->addError($e);
            $this->getMessage()->replace('message', $e instanceof Http? $e->getMessage(): 'We have some problems. Please try later.');
            return $app->json($this->getMessage()->get(), $e instanceof Http? $e->getStatusCode(): Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $app->json("success");
    }

    public function draftAddAction(Application $app, Request $request){
        try {
            $app->getEntityManager()->beginTransaction();

            $firstRexForm = $app->getFormFactory()->create(new FirstRexAddress());
            $firstRexForm->handleRequest($request);

            $this->saveFlyer(
                $app,
                $request,
                new Realtor(),
                (new Queue())->setState(Queue::STATE_DRAFT)->setAdditionalInfo($firstRexForm->getData()),
                new RequestFlyer(),
                ['validation_groups' => ["Default", "draft"]]
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

    public function draftUpdateAction(Application $app, Request $request, $id){
        try {
            $app->getEntityManager()->beginTransaction();
            $queue = $this->getQueueById($app, $id);

            if($queue->getState() !== Queue::STATE_DRAFT){
                throw new Http("We can re-save only draft.");
            }

            $realtor = $this->getRealtorById($app, $queue->getFlyer()->getRealtorId());

            $this->saveFlyer(
                $app,
                $request,
                $realtor,
                $queue,
                $queue->getFlyer(),
                ['method' => 'PUT', 'validation_groups' => ["Default", "draft"]]
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

    public function deleteDraftAction(Application $app, $id){
        try {
            $app->getEntityManager()->beginTransaction();
            $queue = $this->getQueueById($app, $id);

            if($queue->getState() !== Queue::STATE_DRAFT){
                throw new Http("We can remove only draft.");
            }

            $realtor = $this->getRealtorById($app, $queue->getFlyer()->getRealtorId());

            $app->getEntityManager()->remove($queue->getFlyer());
            $app->getEntityManager()->remove($realtor);
            $app->getEntityManager()->remove($queue);
            $app->getEntityManager()->flush();

            $app->getEntityManager()->commit();
        }catch (\Exception $e){
            $app->getEntityManager()->rollback();
            $app->getMonolog()->addError($e);
            $this->getMessage()->replace('message', $e instanceof Http? $e->getMessage(): 'We have some problems. Please try later.');
            return $app->json($this->getMessage()->get(), $e instanceof Http? $e->getStatusCode(): Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $app->json("success");
    }

    public function createFromApprovalRequestAction(Application $app, Request $request, $id){
        try {
            $app->getEntityManager()->beginTransaction();

            /** @var Queue $queue */
            $queue = (new QueueManager($app))->getByIdWithUser($id);

            if(!$queue){
                throw new Http(sprintf("Request flyer \'%s\' not found.", $id), Response::HTTP_BAD_REQUEST);
            }

            if ($app->user()->getId() != $queue->getUser()->getId() && !$app['security']->isGranted(User::ROLE_ADMIN)){
                throw new Http("You do not have privileges.", Response::HTTP_FORBIDDEN);
            }

            $queue->setType(Queue::TYPE_FLYER)->setState(Queue::STATE_LISTING_FLYER_PENDING);

            $realtor = new Realtor();
            $flyer = new RequestFlyer();

            $this->saveFlyer(
                $app,
                $request,
                $realtor,
                $queue,
                $flyer,
                ['method' => 'PUT', 'validation_groups' => ["Default", "fromPropertyApproval"]]
            );

            (new RequestChangeStatus($app, $queue, new RequestFlyerApproval($realtor, $flyer, $request->getSchemeAndHttpHost())))
                ->send();

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