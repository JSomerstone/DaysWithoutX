<?php
namespace JSomerstone\DaysWithout;

use \DerAlex\Silex\YamlConfigServiceProvider,
    \Silex\Provider\TwigServiceProvider,
    \Silex\Provider\SessionServiceProvider,
    \JSomerstone\DaysWithout\Service\StorageServiceProvider,
    \JSomerstone\DaysWithout\Service\ValidationServiceProvider;
use JSomerstone\DaysWithout\Lib\InputValidator;

class Application extends \Silex\Application
{

    /**
     * @param YamlConfigServiceProvider $configService
     * @param string $viewPath
     * @param string $validationRulePath
     * @param array $values
     */
    public function __construct(
        YamlConfigServiceProvider $configService,
        $viewPath,
        $validationRulePath,
        array $values = array())
    {
        parent::__construct($values);

        $this->register($configService);
        $this->register(new TwigServiceProvider(), array('twig.path' => $viewPath));
        $this->register(new SessionServiceProvider());

        $mongoClient = new \MongoClient($this->getConfigOrFail('dwo:storage:server'));
        $databaseName = $this->getConfigOrFail('dwo:storage:database');
        $this->register(new StorageServiceProvider($mongoClient, $databaseName));

        $inputValidator = new InputValidator();
        $this->register(new ValidationServiceProvider($inputValidator, $validationRulePath));
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
     * @return ValidationServiceProvider
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
