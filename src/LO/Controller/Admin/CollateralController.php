<?php
/**
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
use LO\Model\Entity\TemplateCategory;
use LO\Model\Entity\TemplateFormat;
use LO\Form\TemplateType;

class CollateralController extends Base
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

            $this->validation($app, $request, $model);

            $app->getEntityManager()->persist($model);
            $app->getEntityManager()->flush();
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

    public function getCategoriesAction(Application $app)
    {
        return $app->json($app->getEntityManager()->getRepository(TemplateCategory::class)->findAll());
    }

    public function getFormatsAction(Application $app)
    {
        return $app->json($app->getEntityManager()->getRepository(TemplateFormat::class)->findAll());
    }

    private function validation(Application $app, Request $request, Template $model)
    {
        $data = $request->request->get('template');
        $em   = $app->getEntityManager();

        // Set template data
        $form = $app->getFormFactory()->create(
            new TemplateType($app->getS3()),
            $model,
            ['validation_groups' => ['Default']]
        );
        $form->submit($data);

        if (!$form->isValid()) {
            $app->getMonolog()->addError($form->getErrors(true));
            $this->errors = $this->getFormErrors($form);
            throw new BadRequestHttpException(implode(' ', $this->errors));
        }

        // Set template category
        if (
            !empty($data['category']['id'])
            && $category = $em->getRepository(TemplateCategory::class)->find($data['category']['id'])
        ) {
            $model->setCategory($category);
        }
        else {
            throw new BadRequestHttpException('Category not exist.');
        }

        // Set template format
        if (
            !empty($data['format']['id'])
            && $format = $em->getRepository(TemplateFormat::class)->find($data['format']['id'])
        ) {
            $model->setFormat($format);
        }
        else {
            throw new BadRequestHttpException('Format not exist.');
        }

        return $model;
    }
}
