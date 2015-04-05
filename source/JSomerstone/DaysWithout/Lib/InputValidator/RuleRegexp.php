<?php
namespace JSomerstone\DaysWithout\Lib\InputValidator;

class RuleRegexp extends BaseRule implements RuleInterface
{
    public function validate($actualValue)
    {
        return preg_match($this->ruleValue, $actualValue);
    }

    public function getErrorMessage()
    {
        return "Value does not match expected pattern";
    }

}
