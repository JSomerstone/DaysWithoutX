<?php
namespace JSomerstone\DaysWithout\Lib\InputValidator;

class RuleMin extends BaseRule implements RuleInterface
{
    public function validate($actualValue)
    {
        return ($actualValue > $this->ruleValue);
    }

    public function getErrorMessage()
    {
        return "Was larger than " . $this->ruleValue;
    }

} 
