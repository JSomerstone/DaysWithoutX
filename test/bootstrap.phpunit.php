<?php
date_default_timezone_set('Europe/Helsinki');
require_once __DIR__ . '/../source/autoloader.php';
require_once __DIR__ . '/../vendor/autoload.php';

function D()
{
    $params = func_get_args();
    if ($params)
    {
        ob_end_flush();
        foreach ($params as $param)
        {
            var_dump($param);
        }
        echo "---------------\n";
        ob_start();
    }
}

function testloader($className)
{
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

spl_autoload_register('testloader');
