<?php
/**
 * User: Eugene Lysenko
 * Date: 12/21/15
 * Time: 14:58
 */
namespace LO\Controller;

use LO\Application;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use LO\Model\Entity\Template;
use LO\Model\Entity\TemplateCategory;
use LO\Model\Entity\TemplateFormat;
use Doctrine\ORM\Query;
use Knp\Snappy\Pdf;
use Mixpanel;

class TemplateController
{
    public function downloadAction(Application $app, $id)
    {
        if ($template = $app->getTemplateManager()->getById($id)) {
            $user    = $app->getSecurityTokenStorage()->getToken()->getUser();
            $address = $user->getAddress();
            $lender  = $user->getLender();
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

                $html = $app->getTwig()->render('request.template.pdf.twig', [
                    'template' => [
                        'picture' => $template->getPicture()
                    ],
                    'user' => [
                        'firstName' => $user->getFirstName(),
                        'lastName'  => $user->getLastName(),
                        'title'     => $user->getTitle(),
                        'picture'   => $user->getPicture(),
                        'phone'     => $user->getPhone(),
                        'email'     => $user->getEmail(),
                        'nmls'      => $user->getNmls(),
                        'address'   => $address->getFormattedAddress()
                    ],
                    'lender' => [
                        'picture'    => $lender->getPicture(),
                        'disclosure' => $lender->getDisclosureForState($user->getAddress()->getState())
                    ]
                ]);

                // Mixpanel analytics
                $mp = Mixpanel::getInstance($app->getConfigByName('mixpanel', 'token'));
                $mp->identify($user->getId());
                $mp->track('Document Download', ['id' => $template->getId(), 'name' => $template->getName()]);

                $name = strtolower(str_replace (' ', '-', $template->getName()));
                $time = time();

                header('Content-Type: application/pdf');
                header("Content-Disposition: attachment; filename=\"$name-$time.pdf\"");

                echo $pdf->getOutputFromHtml($html, [], true);
            }
            catch (\Exception $e) {
                header_remove('Content-Type');
                header_remove('Content-Disposition');
                return $app->json(['error' => '', 'message' => $e->getMessage()]);
            }
        }

        return $app->json('Error. Document not found');
    }

    public function getCategoriesAction(Application $app)
    {
        $query = $app->getEntityManager()->createQueryBuilder()->select('c')->from(TemplateCategory::class, 'c');
        return $app->json($query->getQuery()->getResult(Query::HYDRATE_ARRAY));
    }

    public function getFormatsAction(Application $app)
    {
        $query = $app->getEntityManager()->createQueryBuilder()->select('f')->from(TemplateFormat::class, 'f');
        return $app->json($query->getQuery()->getResult(Query::HYDRATE_ARRAY));
    }

    private function getById(Application $app, $id)
    {
        if (!($model = $app->getEntityManager()->getRepository(Template::class)->find($id))
            || $model->getDeleted() === '1'
            || $model->getArchive() === '1'
        ) {
            throw new BadRequestHttpException('Collateral not found.');
        }

        return $model;
    }
}
