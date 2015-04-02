<?php

function jsomerstone_autoloader($className)
{
    if ( ! preg_match('/^JSomerstone/', $className))
    {
        return;
    }

    $fileName = sprintf(
        "%s/%s.php",
        __DIR__,
        str_replace('\\', '/', $className)
    );
    if (file_exists($fileName))
    {
        return require_once($fileName);
    }
}

spl_autoload_register('jsomerstone_autoloader');
