<?php
namespace JSomerstone\DaysWithout\Lib\InputValidator;

class RuleEmail extends BaseRule implements RuleInterface
{
    public function validate($actualValue)
    {
        return (bool)preg_match('/.+@[a-z0-9]+([a-z0-9.]+)?/', $actualValue);
    }

    public function getErrorMessage()
    {
        return "Please provide valid email";
    }

} 
