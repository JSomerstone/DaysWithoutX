<?php
namespace JSomerstone\DaysWithout\Lib\InputValidator;

class RuleWhiteList extends BaseRule implements RuleInterface
{
    public function __construct($ruleValue)
    {
        if ( ! is_array($ruleValue))
        {
            throw new RuleFactoryException("RuleWhiteList only accepts array as rule value");
        }
        parent::__construct($ruleValue);
    }

    public function validate($actualValue)
    {
        return in_array($actualValue, $this->ruleValue);
    }

    public function getErrorMessage()
    {
        return "Value is not allowed";
    }

}
