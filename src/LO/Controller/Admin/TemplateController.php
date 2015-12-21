<?php
/**
 * Created by PhpStorm.
 * User: Eugene Lysenko
 * Date: 12/21/15
 * Time: 14:58
 */
namespace LO\Controller\Admin;

use LO\Application;
use Symfony\Component\HttpFoundation\Request;
use LO\Traits\GetFormErrors;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use LO\Model\Entity\Template;

class TemplateController extends Base
{
    use GetFormErrors;

    public function getListAction(Application $app, Request $request)
    {

    }

    public function getAction(Application $app, $id)
    {

    }

    public function addAction(Application $app, Request $request)
    {
        try {
            $app->getEntityManager()->beginTransaction();
            $model = new Template;

            $this->createForm($app, $request, $model);
//
//            $model->setCompany($this->realtyCompanyExist($app, $model->getRealtyCompanyId()));
//
//            $app->getEntityManager()->persist($model);
//            $app->getEntityManager()->flush();
            $app->getEntityManager()->commit();

            return $app->json(['id' => $model->getId()]);
        }
        catch (HttpException $e) {
            $app->getEntityManager()->rollback();
            $app->getMonolog()->addError($e);
            $this->errors['message'] = $e->getMessage();

            return $app->json($this->errors, $e->getStatusCode());
        }
    }

    public function updateAction(Application $app, Request $request, $id)
    {

    }

    public function deleteAction(Application $app, $id)
    {

    }

    private function createForm(Application $app, Request $request, Template $model)
    {

    }
}
