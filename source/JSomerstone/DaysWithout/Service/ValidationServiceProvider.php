<?php
namespace JSomerstone\DaysWithout\Service;

use Silex\Application;
use Silex\ServiceProviderInterface;
use Symfony\Component\Yaml\Yaml;
use JSomerstone\DaysWithout\Lib\InputValidator;
use JSomerstone\DaysWithout\Lib\InputValidatorValueException;

class ValidationServiceProvider implements ServiceProviderInterface
{
    const SERVICE = 'validator';

    /**
     * @var InputValidator
     */
    private $inputValidator;

    /**
     * @var string
     */
    private $rulePath;

    /**
     * @param InputValidator $validator
     * @param string $rulePath
     * @throws \Exception
     */
    public function __construct(InputValidator $validator, $rulePath)
    {
        $this->inputValidator = $validator;
        $this->rulePath = $rulePath;
        if ( ! is_readable($rulePath))
        {
            throw new \Exception("Unreadable input validation rule path: '$rulePath'");
        }
    }

    /**
     * @param Application $app
     */
    public function register(Application $app)
    {
        $app[self::SERVICE] = $this->inputValidator;
        $app['validator.rules'] = Yaml::parse(file_get_contents($this->rulePath));
    }

    /**
     * @param Application $app
     */
    public function boot(Application $app)
    {
        foreach($app['validator.rules'] as $field => $ruleSet)
        {
            $this->inputValidator->setValidationRule($field, $ruleSet);
        }
    }
}
