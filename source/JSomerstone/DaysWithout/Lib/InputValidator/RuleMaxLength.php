<?php
namespace JSomerstone\DaysWithout\Lib\InputValidator;

class RuleMaxLength extends BaseRule implements RuleInterface
{
    public function validate($actualValue)
    {
        return (mb_strlen($actualValue) <= $this->ruleValue);
    }

    public function getErrorMessage()
    {
        return "Cannot be longer than $this->ruleValue characters";
    }

}
