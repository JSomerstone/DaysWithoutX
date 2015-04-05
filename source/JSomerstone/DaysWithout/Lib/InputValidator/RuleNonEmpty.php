<?php
namespace JSomerstone\DaysWithout\Lib\InputValidator;

class RuleNonEmpty extends BaseRule implements RuleInterface
{
    public function validate($actualValue)
    {
        return ! empty ($actualValue);
    }

    public function getErrorMessage()
    {
        return "Value was empty";
    }

}
