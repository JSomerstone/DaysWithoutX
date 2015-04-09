<?php
namespace JSomerstone\DaysWithout\Lib\InputValidator;

class RuleMinLength extends BaseRule implements RuleInterface
{
    public function validate($actualValue)
    {
        return (mb_strlen($actualValue) >= $this->ruleValue);
    }

    public function getErrorMessage()
    {
        return "Must be at least $this->ruleValue characters";
    }

}
