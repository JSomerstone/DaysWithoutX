<?php
/**
 * Created by PhpStorm.
 * User: joona
 * Date: 05/04/15
 * Time: 16:07
 */

namespace JSomerstone\DaysWithout\Lib\InputValidator;

class RuleMax extends BaseRule implements RuleInterface
{
    public function validate($actualValue)
    {
        return ($actualValue <= $this->ruleValue);
    }

    public function getErrorMessage()
    {
        return "Was smaller than " . $this->ruleValue;
    }

} 
