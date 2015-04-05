<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../autoloader.php';

use \Symfony\Component\HttpFoundation\Request,
    \DerAlex\Silex\YamlConfigServiceProvider;

$app = new \JSomerstone\DaysWithout\Application(
    __DIR__ . '/../../config/config.yml',

    $viewPath = __DIR__ . '/../view',
    $validationRulePath = __DIR__ . '/../JSomerstone/DaysWithout/Resources/validation.yml'
);

$request = Request::createFromGlobals();
/**
 * FRONT-PAGE
 */
$app->get('/', function() use ($app)
{
    $controller = $app['controller.default'];
    return $controller->indexAction();
});

/**
 * SHOW COUNTER
 */
$app->get('/{counter}/{owner}', function($counter, $owner) use ($app)
{
    $controller = $app['controller.counter'];
    return $controller->viewCounter($counter, $owner);
});


/**
 * SIGN-UP
 */
$app->post('/api/signup', function(Request $request) use ($app)
{
    $controller = $app['controller.api'];
    return $controller->signupAction(
        $request->get('nick'),
        $request->get('password'),
        $request->get('password-confirm')
    );
});

/**
 * LOGIN
 */
$app->post('/api/login', function(Request $request) use ($app)
{
    $controller = $app['controller.session'];
    return $controller->loginAction(
        $request->get('nick'),
        $request->get('password')
    );
});

/**
 * LOGOUT
 */
$app->post('/api/logout', function(Request $request) use ($app)
{
    $controller = $app['controller.session'];
    return $controller->logoutAction($request);
});

/**
 * GET COUNTER
 */
$app->get('/api/counter/{counter}/{owner}', function($counter, $owner) use ($app)
{
    $controller = $app['controller.counter'];
    return $controller->getCounter($counter, $owner);
});

/**
 * RESET COUNTER
 */
$app->post('/api/counter/{counter}/{owner}', function($counter, $owner, Request $request) use ($app)
{
    $controller = $app['controller.counter'];
    return $controller->resetAction($counter, $owner, $request->get('comment'));
});

/**
 * POST COUNTER
 */
$app->post('/api/counter', function(Request $request) use ($app)
{
    $controller = $app['controller.counter'];
    return $controller->createAction(
        $request->get('headline'),
       $request->get('visibility')
    );
});


$app->get('/api/list/newest/{page}', function ($page) use ($app) {

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

$app->get('/api/list/newest/{page}', function ($page) use ($app)
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

$app->run($request);
