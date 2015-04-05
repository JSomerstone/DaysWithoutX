<?php
namespace JSomerstone\DaysWithout\Lib\InputValidator;

abstract class BaseRule
{
    protected $ruleValue;

    public function __construct($ruleValue)
    {
        $this->ruleValue = $ruleValue;
    }
}
