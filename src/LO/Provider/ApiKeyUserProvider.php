<?php
/**
 * Created by IntelliJ IDEA.
 * User: samoilenko
 * Date: 3/16/15
 * Time: 1:39 PM
 */
/**
 * This class is a UserProvider for the Symfony Security component,
 * implementing its UserProviderInterface
 * @author  David Raison <david@tentwentyfour.lu>
 */

namespace LO\Provider;

use LO\Application;
use LO\Model\Manager\UserManager;

class ApiKeyUserProvider extends UserProvider{
    /** @var  UserManager */
    private $userManager;

    public function __construct(Application $app, UserManager $manager){
        parent::__construct($app);
        $this->userManager = $manager;
    }

    /**
     * Implements getUsernameForApiKey used in the ApiKeyAuthenticator
     *
     * The ApiKeyAuthenticator will throw an exception if the returned value is falsy,
     * so we don't throw any Exception here.
     */
    public function getUsernameForApiKey($apiKey){
        return $this->userManager->findByToken($apiKey);
    }

}