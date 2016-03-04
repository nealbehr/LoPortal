<?php
/**
 * User: Eugene Lysenko
 * Date: 12/21/15
 * Time: 14:58
 */

namespace LO\Controller;

use LO\Application,
    LO\Model\Entity\User,
    LO\Model\Entity\Template,
    LO\Model\Entity\TemplateCategory,
    LO\Model\Entity\TemplateFormat,
    Doctrine\ORM\Query,
    Knp\Snappy\Pdf,
    Mixpanel;

class TemplateController
{
    /**
     * @param Application $app
     * @param integer $id
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function downloadAction(Application $app, $id)
    {
        if ($template = $app->getTemplateManager()->getById($id)) {
            try {
                $user = $app->getSecurityTokenStorage()->getToken()->getUser();
                if ('pdf' === $template->getFileFormat()) {
                    $content = file_get_contents($template->getFile());
                }
                else {
                    $content = $this->createPdf($this->createHtml($app, $template, $user));
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

    /**
     * @param Application $app
     * @param integer $id
     * @return string|\Symfony\Component\HttpFoundation\JsonResponse
     */
    public  function htmlAction(Application $app, $id)
    {
        if ($template = $app->getTemplateManager()->getById($id)) {
            try {
                return ('pdf' === $template->getFileFormat())
                    ? 'File PDF'
                    : $this->createHtml($app, $template, $app->getSecurityTokenStorage()->getToken()->getUser());
            }
            catch (\Exception $e) {
                header_remove('Content-Type');
                header_remove('Content-Disposition');

                return $app->json(['error' => '', 'message' => $e->getMessage()]);
            }
        }

        return $app->json('Error. Document not found');
    }

    /**
     * @param Application $app
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getCategoriesAction(Application $app)
    {
        $query = $app->getEntityManager()->createQueryBuilder()->select('c')->from(TemplateCategory::class, 'c');
        return $app->json($query->getQuery()->getResult(Query::HYDRATE_ARRAY));
    }

    /**
     * @param Application $app
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function getFormatsAction(Application $app)
    {
        $query = $app->getEntityManager()->createQueryBuilder()->select('f')->from(TemplateFormat::class, 'f');
        return $app->json($query->getQuery()->getResult(Query::HYDRATE_ARRAY));
    }

    /**
     * @param Application $app
     * @param Template $template
     * @param User $user
     * @return string
     */
    private function createHtml(Application $app, Template $template, User $user)
    {
        $address = $user->getAddress();
        $lender  = $user->getLender();

        return $app->getTwig()->render('request.template.pdf.twig', [
            'template' => [
                'picture'   => $template->getFile(),
                'coBranded' => $template->isCoBranded(),
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
    }

    /**
     * @param string $html
     * @return string
     */
    private function createPdf($html)
    {
        $pdf = new Pdf();
        $pdf->setBinary('/usr/local/bin/wkhtmltopdf');
        $pdf->setOption('dpi', 300);
        $pdf->setOption('page-width', '8.5in');
        $pdf->setOption('page-height', '11in');
        $pdf->setOption('margin-left', 0);
        $pdf->setOption('margin-right', 0);
        $pdf->setOption('margin-top', 0);
        $pdf->setOption('margin-bottom', 0);

        return $pdf->getOutputFromHtml($html, [], true);
    }
}
