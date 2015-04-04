<?php
function jsomerstone_autoloader($className)
{
    if ( ! preg_match('/^JSomerstone/', $className))
    {
        return;
    }

    $fileName = __DIR__ . '/' . str_replace('\\', '/', $className) . '.php';
    if (file_exists($fileName))
    {
        return require_once($fileName);
    }
}

spl_autoload_register('jsomerstone_autoloader');
