<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../JSomerstone/autoloader.php';

use \Symfony\Component\HttpFoundation\Request,
    \DerAlex\Silex\YamlConfigServiceProvider as Config;

$app = new Silex\Application();
$app['debug'] = true;
$app->register(new Config(__DIR__ . '/../../config/config.yml'));
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/../view',
));

$twigLoader = new Twig_Loader_Filesystem(__DIR__ . '/../view');
$twig = new Twig_Environment($twigLoader);

/**
 * FRONT-PAGE
 */
$app->get('/', function() use ($app, $twig)
{
    return $twig->render(
        'default/index.html.twig',
        array(
            'title' => 'Pohjakuvat',
            'image_path' => '/img',
            'view_path' => '',
            'url' => '/'
        )
    );
});

$app->run();
