<?php
/**
 * Created by PhpStorm.
 * User: joona
 * Date: 05/04/15
 * Time: 16:06
 */

namespace JSomerstone\DaysWithout\Lib\InputValidator;


interface RuleInterface
{
    /**
     * @param mixed $actualValue
     * @return bool
     */
    public function validate($actualValue);

    /**
     * @return string $errorMessage describing validation failure
     */
    public function getErrorMessage();

} 
