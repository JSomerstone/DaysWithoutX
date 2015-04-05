<?php
/**
 * Created by PhpStorm.
 * User: joona
 * Date: 05/04/15
 * Time: 16:06
 */

namespace JSomerstone\DaysWithout\Lib\InputValidator;


abstract class RuleFactory
{

    /**
     * @var array
     */
    private static $supportedValidationRules = array(
        'type'          => 'JSomerstone\DaysWithout\Lib\InputValidator\RuleType',
        'regexp'        => 'JSomerstone\DaysWithout\Lib\InputValidator\RuleRegexp',
        'min'           => 'JSomerstone\DaysWithout\Lib\InputValidator\RuleMin',
        'min-length'    => 'JSomerstone\DaysWithout\Lib\InputValidator\RuleMinLength',
        'max'           => 'JSomerstone\DaysWithout\Lib\InputValidator\RuleMax',
        'max-length'    => 'JSomerstone\DaysWithout\Lib\InputValidator\RuleMaxLength',
        'white-list'    => 'JSomerstone\DaysWithout\Lib\InputValidator\RuleWhiteList',
        'non-empty'     => 'JSomerstone\DaysWithout\Lib\InputValidator\RuleNonEmpty',
        'email'         => 'JSomerstone\DaysWithout\Lib\InputValidator\RuleEmail',
    );

    /**
     * @param string $ruleName
     * @param mixed $ruleValue
     * @return RuleInterface
     * @throws RuleFactoryException
     */
    public static function getRuleFor($ruleName, $ruleValue)
    {
        if ( ! self::supportsRule($ruleName))
        {
            throw new RuleFactoryException("Unsupported rule: '$ruleName'");
        }

        $ruleClass = self::$supportedValidationRules[$ruleName];
        return new $ruleClass($ruleValue);
    }

    /**
     * @param string $ruleName
     * @return bool
     */
    public static function supportsRule($ruleName)
    {
        return isset(self::$supportedValidationRules[$ruleName]);
    }
} 

class RuleFactoryException extends \Exception{}
