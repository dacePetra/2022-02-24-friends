<?php

use App\Controllers\UsersController;
use App\View;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

require_once 'vendor/autoload.php';

$dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) {
    $r->addRoute('GET', '/users', [\App\Controllers\UsersController::class, 'index']);  // 'App\Controllers\UsersController'
    $r->addRoute('GET', '/users/{id:\d+}', [\App\Controllers\UsersController::class, 'show']); //handler: 'App\Controllers\UsersController->show'
}); //Šeit definē adreses un ko tālāk darīt

// Fetch method and URI from somewhere
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Strip query string (?foo=bar) and decode URI
if (false !== $pos = strpos($uri, '?')) {
    $uri = substr($uri, 0, $pos);
}
$uri = rawurldecode($uri);

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);
switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        // ... 404 Not Found
        var_dump("404 Not Found");
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $allowedMethods = $routeInfo[1];
        // ... 405 Method Not Allowed
        var_dump("405 Method Not Allowed");
        break;
    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1];   //$handler = explode('->', $routeInfo[1]);
        $controller = $handler[0];
        $method = $handler[1];

        $vars = $routeInfo[2];

        /** @var View $response */ //lai var getPath un getVariables pasaukt
        $response = (new $controller)->$method($vars);

        $loader = new FilesystemLoader('app/Views'); //filename path
        $twig = new Environment($loader); //noņēmām kašošanu

        //if($response instanceof View)
        echo $twig->render($response->getPath(), $response->getVariables());
        break;
}

