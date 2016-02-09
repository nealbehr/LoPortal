<?php
/**
 * User: Eugene Lysenko
 * Date: 12/21/15
 * Time: 14:58
 */

namespace LO\Controller\Admin;

use LO\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use LO\Traits\GetFormErrors;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use LO\Model\Entity\Template;
use LO\Model\Entity\TemplateCategory;
use LO\Model\Entity\TemplateFormat;
use LO\Model\Entity\TemplateAddress;
use LO\Model\Entity\Lender;
use Doctrine\ORM\Query;
use Doctrine\ORM\EntityManager;
use LO\Form\TemplateType;
use LO\Common\UploadS3\File;
use LO\Exception\Http;

class TemplateController extends Base
{
    use GetFormErrors;

    public function getListAction(Application $app)
    {
        try {
            $query = $app->getEntityManager()
                ->createQueryBuilder()
                ->select('t')
                ->from(Template::class, 't')
                ->where("t.deleted = '0'");
            $templates = $query->getQuery()->getResult(Query::HYDRATE_ARRAY);

            $data = [];
            foreach ($templates as $template) {
                if ('0' === $template['archive']) {
                    $data[$template['category_id']][] = $template;
                }
                else {
                    $data[TemplateCategory::ARCHIVE_CATEGORY][] = $template;
                }
            }

            return $app->json($data);
        }
        catch (HttpException $e) {
            $app->getMonolog()->addWarning($e);
            return $app->json(['message' => $e->getMessage()], $e->getStatusCode());
        }
    }

    public function getAction(Application $app, $id)
    {
        try {
            return $app->json($app->getTemplateManager()->getById($id)->toFullArray());
        }
        catch (HttpException $e) {
            $app->getMonolog()->addWarning($e);
            return $app->json(['message' => $e->getMessage()], $e->getStatusCode());
        }
    }

    public function addAction(Application $app, Request $request)
    {
        $em = $app->getEntityManager();
        try {
            $em->beginTransaction();
            $model = new Template;

            $this->validation($em, $app, $request, $model);

            $em->persist($model);
            $em->flush();
            $em->commit();

            return $app->json(['id' => $model->getId()]);
        }
        catch (HttpException $e) {
            $em->rollback();
            $app->getMonolog()->addError($e);
            $this->errors['message'] = $e->getMessage();

            return $app->json($this->errors, $e->getStatusCode());
        }
    }

    public function updateAction(Application $app, Request $request, $id)
    {
        $em = $app->getEntityManager();
        try {
            $em->beginTransaction();
            $model = $app->getTemplateManager()->getById($id);

            $this->validation($em, $app, $request, $model);

            $em->persist($model);
            $em->flush();
            $em->commit();

            return $app->json($model->toFullArray());
        }
        catch (HttpException $e) {
            $app->getMonolog()->addWarning($e);
            $this->errors['message'] = $e->getMessage();

            return $app->json($this->errors, $e->getStatusCode());
        }
    }

    public function deleteAction(Application $app, $id)
    {
        try {
            $model = $app->getTemplateManager()->getById($id);
            $model->setDeleted('1');
            $app->getEntityManager()->persist($model);
            $app->getEntityManager()->flush();

            return $app->json('success');
        }
        catch (HttpException $e) {
            $app->getMonolog()->addWarning($e);
            $this->errors['message'] = $e->getMessage();

            return $app->json($this->errors, $e->getStatusCode());
        }
    }

    private function validation(EntityManager $em, Application $app, Request $request, Template $model)
    {
        $data = $request->request->get('template');

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

        // Upload and set files
        if (!empty($data['file'])) {
            $fileName = time().mt_rand(1, 100000);
            $file     = new File($app->getS3(), $data['file'], '1rex/tamplate/file');
            $model->setFileFormat($file->getFormat());
            $model->setFile($file->download($fileName));

            $model->setPreviewPicture(
                (new File($app->getS3(), $data['file'], '1rex/tamplate/preview'))
                    ->createPreview()
                    ->download($fileName)
            );
        }
        else {
            throw new Http('File content is empty.', Response::HTTP_BAD_REQUEST);
        }

        // Set template category
        if (
            !empty($data['category_id'])
            && $category = $em->getRepository(TemplateCategory::class)->find($data['category_id'])
        ) {
            $model->setCategory($category);
        }
        else {
            throw new BadRequestHttpException('Category not exist.');
        }

        // Set template format
        if (
            !empty($data['format_id'])
            && $format = $em->getRepository(TemplateFormat::class)->find($data['format_id'])
        ) {
            $model->setFormat($format);
        }
        else {
            throw new BadRequestHttpException('Format not exist.');
        }

        // Set lenders
        if (isset($data['lenders_all']) && $data['lenders_all'] === '0') {
            $model->getLenders()->clear();

            if (!empty($data['lenders'])) {
                $query = $em->createQueryBuilder();
                $query->select('l');
                $query->from(Lender::class, 'l');
                $query->where($query->expr()->in('l.id', $data['lenders']));
                $lenders = $query->getQuery()->getResult();
                if (!empty($lenders)) {
                    foreach ($lenders as $lender) {
                        $model->getLenders()->add($lender);
                    }
                }
            }
        }

        // Set states
        if (isset($data['states_all']) && $data['states_all'] === '0') {
            foreach ($model->getAddresses() as $address) {
                $em->remove($address);
            }
            $em->flush();
            if (!empty($data['states'])) {
                foreach ($data['states'] as $state) {
                    $address = new TemplateAddress();
                    $address->setTemplate($model);
                    $address->setState($state);
                    $em->persist($address);
                }
            }
        }

        return $model;
    }
}
