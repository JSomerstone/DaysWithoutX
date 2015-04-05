<?php
namespace JSomerstone\DaysWithout\Lib\InputValidator;

class RuleType extends BaseRule implements RuleInterface
{
    public function validate($actualValue)
    {
        switch($this->ruleValue)
        {
            case 'int': return is_int($actualValue);
            case 'string': return is_string($actualValue);
            case 'bool': return is_bool($actualValue);
            case 'array': return is_array($actualValue);
        }
    }

    public function getErrorMessage()
    {
        return "Value was not '$this->ruleValue'";
    }

}
