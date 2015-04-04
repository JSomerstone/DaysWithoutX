<?php

namespace JSomerstone\DaysWithout\Lib;


class InputValidator
{
    /**
     * @var array collection of validation rules
     *            array('field' => array('rule-type' => value, ... ), ... )
     */
    private $validationRules = array();

    /**
     * @var array
     */
    private $supportedValidationRules = array(
        'type',
        'regexp',
        'message',
        'custom',
        'min',
        'min-length',
        'max',
        'max-length',
        'non-empty',
        'white-list'
    );

    /**
     * @param array $validationRuleArray
     */
    public function __construct(array $validationRuleArray = array())
    {
        foreach ($validationRuleArray as $field => $ruleSet)
        {
            $this->setValidationRule($field, $ruleSet);
        }
    }

    /**
     * @param string $field
     * @param array $ruleSet Array of rules to validate given field by,
     *              supported:'patter', 'regexp', 'message', 'custom', 'min', 'max', 'non-empty'
     */
    public function setValidationRule($field, array $ruleSet)
    {
        $this->assertOnlySupportedRules($field, $ruleSet);
        $this->validationRules[$field] = $ruleSet;
    }

    private function assertOnlySupportedRules($field, array $ruleSet)
    {
        $ruleNames = array_keys($ruleSet);
        foreach ($ruleNames as $given)
        {
            if ( ! in_array($given, $this->supportedValidationRules))
            {
                throw new InputValidatorException("Unsupported validation rule, field:$field, rule:'$given'");
            }
        }
    }

    /**
     * @param string $field
     * @return array
     * @throws InputValidatorException
     */
    public function getValidationRule($field)
    {
        if ( ! isset($this->validationRules[$field]))
        {
            throw new InputValidatorException("Missing validation rule, field:$field");
        }
        return $this->validationRules[$field];
    }

    /**
     * @param array $fieldValuePairs
     * @return InputValidator $this
     */
    public function validateFields(array $fieldValuePairs)
    {
        foreach ($fieldValuePairs as $field => $value)
        {
            $this->validateField($field, $value);
        }
        return $this;
    }

    /**
     * @param string $fieldName
     * @param mixed $value
     * @throws InputValidatorValueException
     */
    public function validateField($fieldName, $value)
    {
        $validationRules = $this->getValidationRule($fieldName);

        $failedRules = array();

        if (isset($validationRules['type']) && ! $this->validateAgainstType($validationRules['type'], $value))
        {
            $failedRules[] = 'type';
        }
        if (isset($validationRules['regexp']) && ! $this->validateAgainstRegexp($validationRules['regexp'], $value))
        {
            $failedRules[] = 'regexp';
        }
        if (isset($validationRules['min']) && ! $this->validateAgainstMin($validationRules['min'], $value))
        {
            $failedRules[] = 'min';
        }
        if (isset($validationRules['min-length']) && ! $this->validateAgainstMinLength($validationRules['min-length'], $value))
        {
            $failedRules[] = 'min-length';
        }
        if (isset($validationRules['max']) && ! $this->validateAgainstMax($validationRules['max'], $value))
        {
            $failedRules[] = 'max';
        }
        if (isset($validationRules['max-length']) && ! $this->validateAgainstMaxLength($validationRules['max-length'], $value))
        {
            $failedRules[] = 'max-length';
        }
        if (isset($validationRules['custom']) && ! $this->validateAgainstCustomMethod($validationRules['custom'], $value))
        {
            $failedRules[] = 'max';
        }
        if (isset($validationRules['white-list']) && ! $this->validateAgainstWhiteList($validationRules['white-list'], $value))
        {
            $failedRules[] = 'white-list';
        }
        if (isset($validationRules['non-empty']) && empty($value))
        {
            $failedRules[] = 'non-empty';
        }

        if ( ! empty($failedRules))
        {
            throw new InputValidatorValueException(
                $fieldName,
                $failedRules
            );
        }
    }

    /**
     * @param string $pattern
     * @param string $actualValue
     * @return bool
     */
    private static function validateAgainstRegexp($pattern, $actualValue)
    {
        return (bool)preg_match(
            $pattern,
            $actualValue
        );
    }

    /**
     * @param string $expectedType
     * @param string $actualValue
     * @return bool
     */
    private static function validateAgainstType($expectedType, $actualValue)
    {
        switch($expectedType)
        {
            case 'int': return is_int($actualValue);
            case 'string': return is_string($actualValue);
            case 'bool': return is_bool($actualValue);
            case 'array': return is_array($actualValue);
        }
    }

    /**
     * @param string $method Custom method of InputValidator to validate value against
     * @param mixed $value
     * @return bool
     * @throws InputValidatorException
     */
    private function validateAgainstCustomMethod($method, $value)
    {
        if ( ! method_exists($this, $method))
        {
            throw new InputValidatorException(
                "Non-existing custom validation method:$method"
            );
        }

        return (bool)self::$method($value);
    }

    private function validateAgainstWhiteList($whiteList, $actualValue)
    {
        return in_array($actualValue, $whiteList);
    }

    private static function isEmail($value)
    {
        return (bool)preg_match('/.+@[a-z0-9]+([a-z0-9.]+)?/', $value);
    }

    /**
     * @param int $minValue
     * @param int $actualValue
     * @return bool
     */
    private function validateAgainstMin($minValue, $actualValue)
    {
        return $actualValue >= $minValue;
    }

    /**
     * @param int $minLength
     * @param string $actualValue
     * @return bool
     */
    private function validateAgainstMinLength($minLength, $actualValue)
    {
        return mb_strlen($actualValue) >= $minLength;
    }

    /**
     * @param int $maxValue
     * @param int $actualValue
     * @return bool
     * @throws \InputValidatorException
     */
    private function validateAgainstMax($maxValue, $actualValue)
    {
        return $actualValue <= $maxValue;
    }


    /**
     * @param int $maxLength
     * @param string $actualValue
     * @return bool
     */
    private function validateAgainstMaxLength($maxLength, $actualValue)
    {
        return mb_strlen($actualValue) <= $maxLength;
    }

    /**
     * @param string $password
     * @param string $confirmation same password again
     * @throws InputValidatorValueException
     */
    public function validatePassword($password, $confirmation)
    {
        if ( $password !== $confirmation)
        {
            throw new InputValidatorValueException('password', 'Passwords do not match');
        }
        return $this->validateField('password', $password);
    }

    public function validateHeadline($headline)
    {
        $urlSafe = StringFormatter::getUrlSafe($headline);
        return self::validateString('/^[a-z0-9-]+$/', $urlSafe);
    }
}

class InputValidatorException extends \Exception
{

}
class InputValidatorValueException extends \JSomerstone\DaysWithout\Exception\PublicException
{
    /**
     * @var string
     */
    private $invalidField;

    /**
     * @var array
     */
    private $rules;

    public function __construct($fieldName, $rules = array())
    {
        $this->invalidField = $fieldName;
        $this->rules = $rules;

        $message = "Input validation failed";
        parent::__construct($message);
    }

    public function getField()
    {
        return $this->invalidField;
    }

    public function getRules()
    {
        return $this->rules;
    }

    public function __toString()
    {
        return sprintf(
            '%s:%s, field:%s, rules:%s',
            __CLASS__,
            $this->getMessage(),
            $this->invalidField,
            implode(',',$this->rules)
        );
    }
}
