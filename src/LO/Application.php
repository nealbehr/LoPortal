<?php
namespace LO;

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
            ->register(new Provider\ServiceControllerServiceProvider())
            ->register(new Provider\ValidatorServiceProvider())
            ->register(new Provider\SecurityServiceProvider())
            ->register(new DoctrineOrmManagerRegistryProvider())
            ->register(new ApiKeyAuthenticationServiceProvider())
            ->register(new ApiKeyUserServiceProvider())
            ->register(new LOProvider\Managers())
            ->register(new DoctrineOrmServiceProvider(), [
                "orm.custom.functions.numeric" => [
                    "RAND" => "Broadway\\Bridge\\Doctrine2\\Functions\\Rand"
                ]
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
                'pattern' => '^/(partials|authorize/signin|authorize/autocomplete|authorize/reset)',
            ],

            'api' => [
                'pattern' => '^.*$',
//                'switch_user' => array('parameter' => '_switch_user', 'role' => 'ROLE_ALLOWED_TO_SWITCH'),
                'apikey' => true,
                'apiLogout' => array('logout_path' => '/logout'),
                'users'   => $this->share(function (){
                        return new UserProvider($this);
                }),
            ],
        ];

        return $this;
    }

    public function initRoutes(){
        $this->mount('/authorize',  new Controller\AuthorizeProvider());
        $this->mount('/partials',   new Controller\PartialProvider());
        $this->mount('/dashboard',  new Controller\DashboardProvider());
        $this->mount('/user',       new Controller\UserProvider());
        $this->mount('/request',    new Controller\LoRequestProvider());

        $this->match('/', function(){
            $this->getTwig()->addFilter(new \Twig_SimpleFilter('ebase64', 'base64_encode'));
            return $this->getTwig()->render('index.twig');
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
     * @return CryptDigestPasswordEncoder
     */
    public function getEncoderDigest(){
        return $this['security.encoder.digest'];
    }


} 