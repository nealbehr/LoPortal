<?php
namespace LO;

use LO\Exception\Http;
use LO\Model\Entity\User;
use LO\Validator\UniqueValidator;
use Silex\Application\SecurityTrait;
use Silex\Application\UrlGeneratorTrait;
use Silex\Provider;
use LO\Provider\UserProvider;
use LO\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Yaml\Yaml;
use Doctrine\ORM\EntityManager;
use Saxulum\DoctrineOrmManagerRegistry\Silex\Provider\DoctrineOrmManagerRegistryProvider;
use Dflydev\Silex\Provider\DoctrineOrm\DoctrineOrmServiceProvider;
use LO\Model\Manager\UserManager;
use LO\Provider as LOProvider;
use LO\Security\CryptDigestPasswordEncoder;
use LO\Provider\ApiKeyAuthenticationServiceProvider;
use LO\Provider\ApiKeyUserServiceProvider;
use LO\Provider\Amazon;
use LO\Model\Manager\DashboardManager;
use Symfony\Component\Validator\Validator;
use Symfony\Component\Form\FormFactory;

class Application extends \Silex\Application{
    use SecurityTrait;
    use UrlGeneratorTrait;

    private $_configValues = [];

    /**
     * @param array|string $configFilename
     * @param string $pathToConfig
     * @param array $values
     */
    public function __construct($configFilename, $pathToConfig, array $values = array()){
        parent::__construct($values);

        $this->fillConfigFromFiles($configFilename, $pathToConfig);

        foreach($this->getConfigValues() as $key => $value) {
            $this[$key] = $value;
        }
    }

    public function setConfig($key, $value){
        $this->_configValues[$key] = $value;

        return $this;
    }

    /**
     * @param $configFilename
     * @param $pathToConfig
     * @return $this
     * @throws ParseException If the YAML is not valid
     */
    private function fillConfigFromFiles($configFilename, $pathToConfig){
        foreach ((array)$configFilename as $filename) {
            $this->_configValues = array_replace_recursive($this->_configValues, Yaml::parse($pathToConfig . $filename . '.yml'));
        }

        return $this;
    }

    /**
     * @param string  param1, param2, param3.....paramN
     *
     * @return mixed
     */
    public function getConfigByName(){
        if(func_num_args() == 0){
            $this->getMonolog()->addError('Empty parameters list');
            return false;
        }

        $config = $this->getConfigValues();
        foreach(func_get_args() as $v){
            if(!isset($config[$v])){
                $this->getMonolog()->addError(sprintf('Config value \'%s\' not found. Parameters list[%s]',
                        $v, implode(',', func_get_args())
                    )
                );

                return false;
            }

            $config = $config[$v];
        }

        return $config;
    }

    public function getConfigValues(){
        return $this->_configValues;
    }

    public function bootstrap(){
        $this->register(new Provider\MonologServiceProvider(), $this->getConfigByName('monolog.config'))
            ->register(new Provider\DoctrineServiceProvider())
            ->register(new Provider\UrlGeneratorServiceProvider())
            ->register(new LOProvider\UniqueValidatorServiceProvider())
            ->register(new Provider\ServiceControllerServiceProvider())
            ->register(new Provider\ValidatorServiceProvider())
            ->register(new Provider\FormServiceProvider())
            ->register(new Amazon())
            ->register(new LOProvider\Paginator())
            ->register(new Provider\SecurityServiceProvider())
            ->register(new DoctrineOrmManagerRegistryProvider())
            ->register(new ApiKeyAuthenticationServiceProvider())
            ->register(new ApiKeyUserServiceProvider())
            ->register(new LOProvider\Managers())
            ->register(new DoctrineOrmServiceProvider(), [
                "orm.custom.functions.numeric" => [
                    "RAND" => "Broadway\\Bridge\\Doctrine2\\Functions\\Rand"
                ],
            ])
//            ->register(new Provider\FormServiceProvider())
            ->register(new Provider\TwigServiceProvider(), ['twig.path' => __DIR__ . '/../../view'])
            ->register(new Provider\SessionServiceProvider())
            ->register(new Provider\TranslationServiceProvider(),
                ['locale_fallbacks'   => ['en'],
                    'translator.domains' => [],
                ]
            )
        ;

        $this['validator.mapping.class_metadata_factory'] = $this->share(function ($app) {
            foreach (spl_autoload_functions() as $fn) {
                \Doctrine\Common\Annotations\AnnotationRegistry::registerLoader($fn);
            }
            $reader = new \Doctrine\Common\Annotations\AnnotationReader;
            $loader = new \Symfony\Component\Validator\Mapping\Loader\AnnotationLoader($reader);
//            $cache  = extension_loaded('apc') ? new \Symfony\Component\Validator\Mapping\ClassMetadata\ApcCache : null;
            return new \Symfony\Component\Validator\Mapping\ClassMetadataFactory($loader);
        });

        Request::enableHttpMethodParameterOverride();

        $this->setUpFirewall()
             ->setupErrorHandler();

        return $this;
    }

    private function setupErrorHandler(){
        $this->error(function (\Exception $e, $code) {
            $this->getMonolog()->addError($code);
            switch ($e->getCode() || $code) {
                case Response::HTTP_NOT_FOUND:
                    $message      = 'The requested page could not be found.';
                    $responseCode = $code;
                    break;
                case Response::HTTP_METHOD_NOT_ALLOWED:
                    $message      = 'Request method Not Allowed.';
                    $responseCode = $code;
                    break;
                default:
                    $message = 'We are sorry, but something went terribly wrong.';
                    $responseCode = Response::HTTP_INTERNAL_SERVER_ERROR;
            }

            return $this->json(['message' => $message], $responseCode);
        });

        return $this;
    }

    private function setUpFirewall(){
        $this['security.encoder.digest'] = $this->share(function() {
            return new CryptDigestPasswordEncoder();
        });

        $this['security.firewalls'] = [
            'root' => [
                'pattern' => '^/$',
            ],
            'login' => [
                'pattern' => '^/(partials|authorize/)',
            ],

            'api' => [
                'pattern' => '^.*$',
//                'form' => ['login_path' => '/#/login'],
                'apikey' => true,
                'switch_user' => array('parameter' => '_switch_user', 'role' => 'ROLE_ALLOWED_TO_SWITCH'),
                'apiLogout' => array('logout_path' => '/logout'),
                'users'   => $this->share(function (){
                        return $this->getUserProvider();
                }),
            ],
        ];

        $this['security.role_hierarchy'] = [
            User::ROLE_ADMIN => [User::ROLE_USER, "ROLE_ALLOWED_TO_SWITCH"]
        ];

        $this['security.access_rules'] = [
            ['^/admin'  , User::ROLE_ADMIN],
        ];

        return $this;
    }

    public function initRoutes(){
        $this->mount('/authorize',  new Controller\AuthorizeProvider());
        $this->mount('/partials',   new Controller\PartialProvider());
        $this->mount('/dashboard',  new Controller\DashboardProvider());
        $this->mount('/user',       new Controller\UserProvider());
        $this->mount('/request',    new Controller\RequestProvider());
        $this->mount('/queue',      new Controller\QueueProvider());
        $this->mount('/admin',      new Controller\AdminProvider());
        $this->mount('/settings',   new Controller\SettingsProvider());

        $this->match('/', function(){
            $this->getTwig()->addFilter(new \Twig_SimpleFilter('ebase64', 'base64_encode'));
            return $this->getTwig()->render('index.twig');
        })
        ->bind('index');

        $this->get('/login', function(){
            throw new Http("Bad credentials", Response::HTTP_FORBIDDEN);
        });

        $this->get('/switch', function(){
            return $this->redirect('/');
        });

        return $this;
    }

    /**
     * @return \Monolog\Logger
     */
    public function getMonolog(){
        return $this['monolog'];
    }

    /**
     * @return \Twig_Environment
     */
    public function getTwig(){
        return $this['twig'];
    }

    /**
     * @return EntityManager
     */
    public final function getEntityManager() {
        return $this['orm.em'];
    }

    /**
     * @return UserManager
     */
    public function getUserManager(){
        return $this['manager.user'];
    }

    /**
     * @return DashboardManager
     */
    public function getDashboardManager(){
        return $this['manager.dashboard'];
    }

    /**
     * @return CryptDigestPasswordEncoder
     */
    public function getEncoderDigest(){
        return $this['security.encoder.digest'];
    }

    /**
     * @return Validator
     */
    public function getValidator(){
        return $this['validator'];
    }

    /**
     * @return \Aws\S3\S3Client
     */
    public function getS3(){
        return $this['amazon.s3'];
    }

    /**
     * @return FormFactory
     */
    public final function getFormFactory() {
        return $this["form.factory"];
    }

    /**
     * @return \Knp\Component\Pager\Paginator
     */
    public function getPaginator(){
        return $this['paginator'];
    }

    /**
     * @return \Aws\Ses\SesClient
     */
    public function getSes(){
        return $this['amazon.ses'];
    }

    /**
     * @return UserProvider
     */
    public function getUserProvider(){
        return $this['user.provider'];
    }

    /**
     * @return \Symfony\Component\Security\Core\SecurityContext
     */
    public function getSecurity(){
        return $this['security'];
    }
}