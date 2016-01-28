<?php
/**
 * User: Eugene Lysenko
 * Date: 12/21/15
 * Time: 14:58
 */
namespace LO\Controller;

use LO\Application;
use LO\Model\Entity\User;
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
            $user     = $app->getSecurityTokenStorage()->getToken()->getUser();
            try {
                if ('pdf' === $template->getFileFormat()) {
                    $content = file_get_contents($template->getFile());
                }
                else {
                    $content = $this->createPdf($app, $template, $user);
                }

                $name = strtolower(str_replace (' ', '-', $template->getName()));
                $time = time();

                // Mixpanel analytics
                $mp = Mixpanel::getInstance($app->getConfigByName('mixpanel', 'token'));
                $mp->identify($user->getId());
                $mp->track('Document Download', ['id' => $template->getId(), 'name' => $template->getName()]);

                header('Content-Type: application/pdf');
                header("Content-Disposition: attachment; filename=\"$name-$time.pdf\"");

                echo $content;
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

    private function createPdf(Application $app, Template $template, User $user)
    {
        $address = $user->getAddress();
        $lender  = $user->getLender();

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
                'picture'   => $template->getFile(),
                'coBranded' => !((bool)$template->getLendersAll() && (bool)$template->getStatesAll()),

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

        return $pdf->getOutputFromHtml($html, [], true);
    }
}
