<?php
namespace LO;

use Silex\Application\SecurityTrait;
use Silex\Application\UrlGeneratorTrait;
use Silex\Provider;
use LO\Provider\UserProvider;
use LO\Security\Firewall\Listener;
use LO\Security\Authentication\Provider as AuthenticationProvider;
use LO\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Yaml\Yaml;
use Doctrine\ORM\EntityManager;
use Saxulum\DoctrineOrmManagerRegistry\Silex\Provider\DoctrineOrmManagerRegistryProvider;
use Dflydev\Silex\Provider\DoctrineOrm\DoctrineOrmServiceProvider;
use LO\Model\Manager\User;
use LO\Provider as LOProvider;

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

        $typeFirewall = 'fc';
        $this['security.authentication_listener.factory.'.$typeFirewall] = $this->protect(function ($name, $options) use ($typeFirewall) {
            // define the authentication provider object
            $this['security.authentication_provider.'.$name.'.'.$typeFirewall] = $this->share(function () {
                return new AuthenticationProvider(new UserProvider($this),
                                                  $this->getConfigByName('user', 'token.expire')
                );
            });

            // define the authentication listener object
            $this['security.authentication_listener.'.$name.'.'.$typeFirewall] = $this->share(function (){
                return new Listener($this['security'], $this['security.authentication_manager']);
            });

            return [
                'security.authentication_provider.'.$name.'.'.$typeFirewall,// the authentication provider id
                'security.authentication_listener.'.$name.'.'.$typeFirewall,// the authentication listener id
                null,// the entry point id
                'pre_auth'// the position of the listener in the stack
            ];
        });

        $this['security.firewalls'] = [
            'root' => [
                'pattern' => '^/$',

            ],
            'login' => [
                'pattern' => '^/(partials|login|authorize/autocomplete)',
            ],

            $typeFirewall => [
                'pattern' => '^.*$',
//                'switch_user' => array('parameter' => '_switch_user', 'role' => 'ROLE_ALLOWED_TO_SWITCH'),
                #'stateless'   => true,
                $typeFirewall => true,
                'users'   => $this->share(function (){
                        return new UserProvider($this);
                    }),
            ],
        ];

        return $this;
    }

    public function initRoutes(){
        $this->mount('/authorize', new Controller\AuthorizeProvider());
        $this->mount('/partials',   new Controller\PartialProvider());
        $this->mount('/dashboard',          new Controller\DashboardProvider());

        $this->match('/', function(){
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
     * @return User
     */
    public function getUserManager(){
        return $this['manager.user'];
    }


} 