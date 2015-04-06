<?php

namespace JSomerstone\DaysWithout\Lib;
use \JSomerstone\DaysWithout\Exception\PublicException,
    JSomerstone\DaysWithout\Lib\InputValidator\RuleFactory;

class InputValidator
{
    /**
     * @var array collection of validation rules
     *            array('field' => array('rule-type' => value, ... ), ... )
     */
    private $validationRules = array();

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
     * @see RuleFactory
     */
    public function setValidationRule($field, array $ruleSet)
    {
        $this->assertOnlySupportedRules($ruleSet);
        $this->validationRules[$field] = $ruleSet;
    }

    private function assertOnlySupportedRules(array &$ruleSet)
    {
        $ruleNames = array_keys($ruleSet);
        foreach ($ruleNames as $given)
        {
            if ( ! RuleFactory::supportsRule($given))
            {
                unset($ruleSet[$given]);
            }
        }
    }

    /**
     * @return array
     */
    public function getValidationRules()
    {
        return $this->validationRules;
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


        foreach ($validationRules as $ruleName => $ruleValue)
        {
            $validator = RuleFactory::getRuleFor($ruleName, $ruleValue);
            if ( ! $validator->validate($value))
            {
                throw new InputValidatorValueException(
                    $fieldName . ': ' . $validator->getErrorMessage(),
                    $fieldName,
                    $ruleName,
                    $ruleValue
                );
            }
        }
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
            throw new InputValidatorValueException(
                'Passwords do not match',
                'password'
            );
        }
        return $this->validateField('password', $password);
    }
}

class InputValidatorException extends \Exception
{

}
class InputValidatorValueException extends PublicException
{
    /**
     * @var string
     */
    private $invalidField;

    /**
     * @var string
     */
    private $rule;

    /**
     * @var mixed
     */
    private $ruleValue;

    public function __construct($publicMessage, $fieldName, $ruleName = null, $ruleValue = null)
    {
        $this->invalidField = $fieldName;
        $this->rule = $ruleName;
        $this->ruleValue = $ruleValue;

        parent::__construct($publicMessage);
    }

    public function getData()
    {
        return array(
            'message' => $this->message,
            'field' => $this->invalidField,
            'rule' => $this->rule,
            'ruleValue' => $this->ruleValue
        );
    }

    public function __toString()
    {
        $string = __CLASS__ . ': ';
        foreach ($this->getData() as $key => $value)
        {
            $string .= "$key:$value, ";
        }
        return $string;
    }
}
