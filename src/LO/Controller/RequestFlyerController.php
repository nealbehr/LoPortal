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
use LO\Form\QueueType;
use LO\Model\Entity\Queue;
use LO\Model\Entity\Realtor;
use LO\Model\Entity\User;
use LO\Model\Manager\QueueManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Knp\Snappy\Pdf;
use \Doctrine\ORM\Query;
use LO\Common\RequestTo1Rex;
use \Mixpanel;


class RequestFlyerController extends RequestFlyerBase
{
    public function getAction(Application $app, $id)
    {
        $queue     = $this->getQueueById($app, $id);
        $queueForm = $app->getFormFactory()->create(new QueueType($app->getS3()), $queue);

        return $app->json([
            'realtor_id' => ($realtor = $queue->getRealtor()) ? $realtor->getId() : null,
            'property'   => array_merge($this->getFormFieldsAsArray($queueForm), ['state' => $queue->getState()]),
            //'realtor'    => $realtor->getPublicInfo(),
            'address'    => $queue->getAdditionalInfo(),
            'user'       => $queue->getUser()->getPublicInfo(),
        ]);
    }

    /**
     * Download request flyer in PDF
     * @param Application $app
     * @param $id
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function download(Application $app, $id)
    {
        // Mixpanel analytics
        if (null !== ($user = $app->getSecurityTokenStorage()->getToken()->getUser())) {
            $mp = Mixpanel::getInstance($app->getConfigByName('mixpanel', 'token'));
            $mp->identify($user->getId());
            $mp->track('Flyer Download');
        }

        $queue = $this->getQueueById($app, $id);
        if($queue) {

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
                $pdfFile = 'flyer-' . $id . '-'. $time . '.pdf';
                $html = $app->getTwig()->render('request.flyer.pdf.twig', $this->getPDFData($queue));

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
        $queue = $this->getQueueById($app, $id);
        if($queue) {
            return $app->getTwig()->render('request.flyer.pdf.twig', $this->getPDFData($queue));
        }
        return $app->json("Error. Flyer not found");
    }

    private function getPDFData(Queue $queue) {

        setlocale(LC_MONETARY, 'en_US');

        $realtor = $queue->getRealtor();
        $loanOfficer = $queue->getUser();
        $lender = $loanOfficer->getLender();

        $realtorPhoto = $realtor->getPhoto();
        if($realtorPhoto == null) {
            $realtorPhoto = 'https://s3-us-west-1.amazonaws.com/1rex/realtor/empty-user.png';
        }
        $propertyPhoto = $queue->getPhoto();
        if($propertyPhoto == null) {
            $propertyPhoto = 'https://s3-us-west-1.amazonaws.com/1rex/property/empty-big.png';
        }

        $discountPart = ((1 - $queue->getMaximumLoan()/100) - $queue->getFundedPercentage()/100) * 100;
        $discount = ((1 - $queue->getMaximumLoan()/100) - $queue->getFundedPercentage()/100) * $queue->getListingPrice();
        $data = [
            'homeImage' => $propertyPhoto,
            'discuontPart' => $discountPart,
            'maxiumLoanAmount' => $queue->getMaximumLoan(),
            'discuont' => number_format($discount, 0, '.', ','),
            'listingPrice' => number_format($queue->getListingPrice(), 0, '.', ','),
            'availableLoan' => number_format($queue->getListingPrice() * $queue->getMaximumLoan() / 100, 0, '.', ','),
            'requiredDownPayment' => number_format($queue->getListingPrice() * (1 - $queue->getMaximumLoan() / 100), 0, '.', ','),
            'ourDownPayment' => number_format($queue->getListingPrice() * $queue->getFundedPercentage()/100, 0, '.', ','),
            'yourDownPayment' => number_format($discount, 0, '.', ','),
            'photoCard1' => $loanOfficer->getPicture(),
            'nameCard1' => $loanOfficer->getFirstName() . ' '. $loanOfficer->getLastName(),
            'agencyCard1' => $lender->getPicture(),
            'lenderDisclosure' => $lender->getDisclosureForState($loanOfficer->getAddress()->getState()),
            'photoCard2' => $realtorPhoto,
            'nameCard2' => $realtor->getFirstName() . ' ' . $realtor->getLastName(),
            'agencyCard2' => $realtor->getRealtyLogo(),
            'omitRealtorInfo' => ($queue->getOmitRealtorInfo() === '0')
        ];

        $data['homeAddress'] = str_replace(
            ', USA',
            '',
            preg_replace('/,/', sprintf(' %s<br/>', $queue->getApartment()), $queue->getAddress(), 1)
        );

        $data['infoCard1']   = sprintf(
            '%s<br/>Ph: %s<br/>%s<br/>%s<br/>%s<br/>NMLS #%s',
            $loanOfficer->getTitle(),
            $loanOfficer->getPhone(),
            $loanOfficer->getEmail(),
            $lender->getName(),
            preg_replace(
                ['/,/', '/,/'],
                ['<br/>', sprintf(' %s<br/>', $loanOfficer->getAddress()->getApartment())],
                $loanOfficer->getAddress()->getFormattedAddress(),
                1
            ),
            $loanOfficer->getNmls()
        );

        $data['infoCard2']   = sprintf('Realtor<sup>®</sup><br/>%s<br/>%s', $realtor->getPhone(), $realtor->getEmail())
            .((empty($realtor->getBreNumber())) ?: sprintf('<br/>CA BRE #%s', $realtor->getBreNumber()));

        return $data;
    }

    public function addAction(Application $app, Request $request){
        try {
            $formOptions = ['validation_groups' => ["Default", "main"]];
            $app->getEntityManager()->beginTransaction();

            $user         = $app->getSecurityTokenStorage()->getToken()->getUser();
            $firstRexForm = $app->getFormFactory()->create(new FirstRexAddress());
            $firstRexForm->handleRequest($request);

            if(!$firstRexForm->isValid()){
                throw new Http('Additional info is not valid', Response::HTTP_BAD_REQUEST);
            }

            $queue = (new Queue())
                ->setListingPrice(0)
                ->setAdditionalInfo($firstRexForm->getData())
                ->setUserType(Queue::TYPE_USER_SELLER);

            // Do not change queue user when edited by admin
            if ($queue->getUser() == null) {
                $queue->setUser($user);
            }

            $app->getEntityManager()->persist($queue);
            $app->getEntityManager()->flush();

            // Get first rex id
            $rexId = (new RequestTo1Rex($app))
                ->setAddress($firstRexForm->getData())
                ->setUser($user)
                ->setQueue($queue)
                ->setType(RequestTo1Rex::TYPE_LISTING_FLYER)
                ->send();
            $queue->set1RexId($rexId);

            $realtor = new Realtor();

            $this->saveFlyer(
                $app,
                $request,
                $realtor,
                $queue,
                $formOptions
            );

            $changeStatusEmail = new RequestChangeStatus($app,  $queue, new RequestFlyerSubmission($realtor, $queue));
            $changeStatusEmail->send();

            $app->getEntityManager()->commit();
        }catch (\Exception $e){
            $app->getEntityManager()->rollback();
            $app->getMonolog()->addError($e);
            $this->getMessage()->replace('message', $e instanceof Http? $e->getMessage(): 'We have some problems. Please try later.');
            return $app->json($this->getMessage()->get(), $e instanceof Http? $e->getStatusCode(): Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $app->json("success");
    }

    public function updateAction(Application $app, Request $request, $id) {
        try {
            $app->getEntityManager()->beginTransaction();
            $queue = $this->getQueueById($app, $id);

            if($queue->getState() !== Queue::STATE_DRAFT) {
                throw new Http("You can't submit this flyer.", Response::HTTP_BAD_REQUEST);
            }

            $formBuilder =  $app->getFormFactory()->createBuilder(new FirstRexAddress());
            $formBuilder->setMethod('PUT');
            $firstRexForm = $formBuilder->getForm();
            $firstRexForm->handleRequest($request);
            $queue->setAdditionalInfo($firstRexForm->getData());

            // Get first rex id
            $billboardID = (new RequestTo1Rex($app))
                ->setAddress($firstRexForm->getData())
                ->setUser($app->getSecurityTokenStorage()->getToken()->getUser())
                ->setQueue($queue)
                ->setType(RequestTo1Rex::TYPE_LISTING_FLYER)
                ->send();

            $app->getMonolog()->addInfo("billboard id: " . $billboardID);
            $queue->set1RexId($billboardID);
            $queue->setState(Queue::STATE_REQUESTED);

            $this->saveFlyer(
                $app,
                $request,
                $queue->getRealtor(),
                $queue,
                ['method' => 'PUT', 'validation_groups' => ["Default", "main"]]
            );

            $changeStatusEmail = new RequestChangeStatus($app,  $queue, new RequestFlyerSubmission($queue->getRealtor(), $queue));
            $changeStatusEmail->send();

            $app->getEntityManager()->commit();
        } catch (\Exception $e){
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

    public function flyerUpdateAction(Application $app, Request $request, $id) {
        try {
            $app->getEntityManager()->beginTransaction();
            $queue = $this->getQueueById($app, $id);
            $formBuilder =  $app->getFormFactory()->createBuilder(new FirstRexAddress());
            $formBuilder->setMethod('PUT');
            $firstRexForm = $formBuilder->getForm();
            $firstRexForm->handleRequest($request);
            $queue->setAdditionalInfo($firstRexForm->getData());
            $realtor = $queue->getRealtor();
            $validationGroup = "draft";
            if($queue->getState() == Queue::STATE_APPROVED) {
                $validationGroup = "approved";
            }
            $this->saveFlyer(
                $app,
                $request,
                $realtor,
                $queue,
                ['method' => 'PUT', 'validation_groups' => ["Default", $validationGroup]]
            );

            $app->getEntityManager()->commit();
        } catch (\Exception $e) {
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

            $realtor = $queue->getRealtor();

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

    public function draftFromFlyerUpdateAction(Application $app, Request $request, $id){
        try {
            $app->getEntityManager()->beginTransaction();

            /** @var Queue $queue */
            $queue = (new QueueManager($app))->getByIdWithUser($id);

            if(!$queue){
                throw new Http(sprintf("Request flyer '%s' not found.", $id), Response::HTTP_BAD_REQUEST);
            }

            if ($app->getSecurityTokenStorage()->getToken()->getUser()->getId() != $queue->getUser()->getId() && !$app->getAuthorizationChecker()->isGranted(User::ROLE_ADMIN)){
                throw new Http("You do not have privileges.", Response::HTTP_FORBIDDEN);
            }

            $firstRexForm = $app->getFormFactory()->create(new FirstRexAddress());
            $firstRexForm->handleRequest($request);

            $queue->setType(Queue::TYPE_FLYER)->setState(Queue::STATE_DRAFT)->setAdditionalInfo($firstRexForm->getData());

            $this->saveFlyer(
                $app,
                $request,
                new Realtor(),
                $queue,
                [
                    'validation_groups' => ["Default", "draft"],
                    'method' => 'PUT',
                ]
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

    public function createFromApprovalRequestAction(Application $app, Request $request, $id){
        try {
            $app->getEntityManager()->beginTransaction();

            /** @var Queue $queue */
            $queue = (new QueueManager($app))->getByIdWithUser($id);

            if(!$queue){
                throw new Http(sprintf("Request flyer '%s' not found.", $id), Response::HTTP_BAD_REQUEST);
            }

            if ($app->getSecurityTokenStorage()->getToken()->getUser()->getId() != $queue->getUser()->getId() && !$app->getAuthorizationChecker()->isGranted(User::ROLE_ADMIN)){
                throw new Http("You do not have privileges.", Response::HTTP_FORBIDDEN);
            }

            $queue->setType(Queue::TYPE_FLYER)->setState(Queue::STATE_REQUESTED);

            $realtor = new Realtor();

            $this->saveFlyer(
                $app,
                $request,
                $realtor,
                $queue,
                ['method' => 'PUT', 'validation_groups' => ["Default", "fromPropertyApproval"]]
            );

            (new RequestChangeStatus($app, $queue, new RequestFlyerApproval($realtor, $queue, $request->getSchemeAndHttpHost())))
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

    public function getRealtorListAction(Application $app)
    {
        $user  = $app->getSecurityTokenStorage()->getToken()->getUser();
        $query = $app->getEntityManager()
            ->createQueryBuilder()
            ->select('r')
            ->from(Realtor::class, 'r')
            ->orderBy('r.first_name', 'asc');

        // if = admin, show all realtors
        if (!$app->getAuthorizationChecker()->isGranted(User::ROLE_ADMIN)) {
            $query->where('r.user_id = :id')->setParameter('id', $user->getId());
        }

        return $app->json(['realtors' => $query->getQuery()->getResult(Query::HYDRATE_ARRAY)]);
    }
}
