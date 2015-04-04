<?php
namespace JSomerstone\DaysWithout;

use \DerAlex\Silex\YamlConfigServiceProvider,
    \Silex\Provider\TwigServiceProvider,
    \Silex\Provider\SessionServiceProvider,
    \Silex\Provider\MonologServiceProvider,
    \JSomerstone\DaysWithout\Service\StorageServiceProvider,
    \JSomerstone\DaysWithout\Service\ValidationServiceProvider;
use JSomerstone\DaysWithout\Controller\ApiController;
use JSomerstone\DaysWithout\Controller\CounterController;
use JSomerstone\DaysWithout\Controller\DefaultController;
use JSomerstone\DaysWithout\Controller\SessionController;
use JSomerstone\DaysWithout\Lib\InputValidator,
    JSomerstone\DaysWithout\Service\ContextService;

class Application extends \Silex\Application
{

    /**
     * @var ContextService
     */
    public $context;

    /**
     * @param string $configPath
     * @param string $viewPath
     * @param string $validationRulePath
     * @param array $values
     */
    public function __construct(
        $configPath,
        $viewPath,
        $validationRulePath,
        array $values = array())
    {
        parent::__construct($values);

        $this->register(new YamlConfigServiceProvider($configPath));

        $mongoClient = new \MongoClient($this->getConfigOrFail('dwo:storage:server'));
        $databaseName = $this->getConfigOrFail('dwo:storage:database');
        $logFile = $this->getConfigOrFail('dwo:log:file');
        $inputValidator = new InputValidator();

        $this->register(new TwigServiceProvider(), array('twig.path' => $viewPath))
            ->register(new SessionServiceProvider())
            ->register(new StorageServiceProvider($mongoClient, $databaseName))
            ->register(new ValidationServiceProvider($inputValidator, $validationRulePath))
            ->register(new MonologServiceProvider(), array('monolog.logfile' => $logFile,));

        $this->registerAs('controller.api', new ApiController())
            ->registerAs('controller.default', new CounterController())
            ->registerAs('controller.counter', new CounterController())
            ->registerAs('controller.session', new SessionController());
    }

    /**
     * @param string $name
     * @param mixed $object
     * @return Application $this
     */
    private function registerAs($name, $object)
    {
        $this[$name] = $object;

        if( method_exists($object, 'register'))
        {
            $object->register($this);
        }

        return $this;
    }

    /**
     * @return StorageServiceProvider
     */
    public function getStorageService()
    {
        return $this['storage'];
    }

    /**
     * @return \Twig_Environment
     */
    public function getTwig()
    {
        return $this['twig'];
    }

    /**
     * @return InputValidator
     */
    public function getValidator()
    {
        return $this['validator'];
    }

    /**
     * @param string|null $key setting path like 'dwo:storage:database'
     * @return array
     * @throws \Exception
     */
    public function getConfig($key = null)
    {
        if (is_null($key))
        {
            return $this['config'];
        }

        $reference = $this['config'];
        foreach (explode(':', $key) as $settingLevel)
        {
            if ( ! isset($reference[$settingLevel]))
            {
                return null;
            }
            $reference = $reference[$settingLevel];
        }
        return $reference;
    }

    /**
     * @param string $key setting path like 'dwo:storage:database'
     * @return array|string
     * @throws \Exception if given setting path is not found
     */
    public function getConfigOrFail($key)
    {
        $value = $this->getConfig($key);
        if (is_null($value))
        {
            throw new \Exception("Missing required configuration '$key'");
        }
        return $value;
    }

    /**
     * @return \Monolog\Logger
     */
    public function getLogger()
    {
        return $this['monolog'];
    }

    public function debug($bool)
    {
        $this['debug'] = $bool;
    }

    public static function D()
    {
        $params = func_get_args();
        if ($params)
        {
            @ob_end_flush();
            foreach ($params as $param)
            {
                var_dump($param);
            }
            echo "---------------\n";
            @ob_start();
        }
    }
} 
