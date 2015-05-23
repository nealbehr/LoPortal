<?php
/**
 * Created by IntelliJ IDEA.
 * User: mac
 * Date: 5/23/15
 * Time: 16:07
 */

namespace LO\Controller;

use LO\Application;
use LO\Model\Entity\RealtyCompany;
use Symfony\Component\HttpFoundation\Request;


class UserRealtyCompanyController extends RequestBaseController {

    public function getAction(Application $app, Request $request) {
        try {
            $em = $app->getEntityManager();
            $allCompanies = $em->getRepository(RealtyCompany::class)->findAll();
            $result = [];
            foreach ($allCompanies as $company) {
                /* @var RealtyCompany $company */
                $result[] = $company->toArray();
            }
            return $app->json($result);
        } catch (\Exception $ex) {
            $app->getMonolog()->addWarning($ex);
        }
        return $app->json([]);
    }
} 