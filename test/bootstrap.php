<?php
date_default_timezone_set('Europe/Helsinki');
include_once __DIR__ . '/../app/bootstrap.php.cache';

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
