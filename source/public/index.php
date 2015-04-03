<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../autoloader.php';

use \Symfony\Component\HttpFoundation\Request,
    \DerAlex\Silex\YamlConfigServiceProvider;

$app = new \JSomerstone\DaysWithout\Application(
    new YamlConfigServiceProvider(__DIR__ . '/../../config/config.yml'),
    $viewPath = __DIR__ . '/../view',
    $validationRulePath = __DIR__ . '/../JSomerstone/DaysWithout/Resources/validation.yml'
);

$request = Request::createFromGlobals();

/**
 * FRONT-PAGE
 */
$app->get('/', function() use ($app)
{
    return $app->getTwig()->render(
        'default/index.html.twig',
        array(
            'title' => 'Days Without ?',
            'succession' => 'Random succession goes here',
            'counter_title' => null,
            'field' => array(
                'headline' => array(
                    'pattern' => '/^.+$/',
                    'title' => 'Without what?'
                )
            ),
            'loggedIn' => false,
            'url' => '/',
            'latest' => $app->getStorageService()->getCounterStorage()->getLatestCounters(10),
            'resentResets' => $app->getStorageService()->getCounterStorage()->getResentResetsCounters(10),
        )
    );
});

$api = $app['controllers_factory'];
$api->post('/signup', function() use ($app, $request)
{
    $nick = $request->get('nick');
    $password = $request->get('password');
    $password2 = $request->get('password-confirm');

    $errors = $app->getValidator()->validateFields(array(
        'nick' => $nick,
        'password' => $password,
    ));

    if ( ! empty($errors))
    {
        return $app->json(array(
            'success' => false,
            'message' => '',
            'data' => $errors
        ));
    }
});


$api->get('/list/newest/{page}', function ($page) use ($app) {

    if ( ! preg_match('/^[1-9]([0-9]+)?$/', $page))
    {
        return $app->abort(400);
    }
    $result = $app->getStorageService()
        ->getCounterStorage()
        ->getLatestCounters(10, 10*(int)$page);

    return $app->json(array(
        'success' => true,
        'data' => $result
    ));
})
->value('page', 1);

$api->get('/list/newest/{page}', function ($page) use ($app)
{
    if ( ! preg_match('/^[1-9]([0-9]+)?$/', $page))
    {
        return $app->abort(400);
    }
    return json_encode(
        $app->getStorageService()
            ->getCounterStorage()
            ->getLatestCounters(10, 10*(int)$page)
    );
})
->value('page', 1);

$app->mount('/api', $api);
$app->run();
