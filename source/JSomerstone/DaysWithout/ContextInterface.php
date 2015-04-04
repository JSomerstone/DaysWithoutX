<?php
namespace JSomerstone\DaysWithout;

use JSomerstone\DaysWithout\ControllerService;
use JSomerstone\DaysWithout\Service\ContextService;


interface ContextInterface
{
    /**
     * Get name a controller is registered by
     * @return string
     */
    public function getRegisterName();

    public function register(ContextService $context);

}
