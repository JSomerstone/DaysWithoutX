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

function D()
{
    $filehandle = fopen('/tmp/aatu.log', 'w');
    foreach (func_get_args() as $argument)
    {
        fwrite($filehandle, var_export($argument, true) . "\n");
    }
    fclose($filehandle);
}
