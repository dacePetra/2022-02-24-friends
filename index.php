<?php
session_start();

use App\Controllers\ArticlesController;
use App\Controllers\UsersController;
use App\Controllers\WelcomeController;
use App\Redirect;
use App\Views\View;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

require_once 'vendor/autoload.php';

$dispatcher = FastRoute\simpleDispatcher(function (FastRoute\RouteCollector $r) {
    //Šeit definē adreses un ko tālāk darīt:
    $r->addRoute('GET', '/', [WelcomeController::class, 'welcome']);

    // Users
    $r->addRoute('GET', '/users/signup', [UsersController::class, 'signup']);
    $r->addRoute('POST', '/users', [UsersController::class, 'register']);

    $r->addRoute('GET', '/users/error', [UsersController::class, 'error']);
    $r->addRoute('GET', '/users/email', [UsersController::class, 'email']);

    $r->addRoute('GET', '/users/login', [UsersController::class, 'login']);
    $r->addRoute('POST', '/users/login', [UsersController::class, 'enter']);

    $r->addRoute('GET', '/users', [UsersController::class, 'index']);
    $r->addRoute('GET', '/users/{id:\d+}', [UsersController::class, 'show']);

    // Articles
    $r->addRoute('GET', '/articles', [ArticlesController::class, 'index']); //'GET', '/articles' != 'POST', '/articles',
    $r->addRoute('GET', '/articles/{id:\d+}', [ArticlesController::class, 'show']);

    $r->addRoute('GET', '/articles/create', [ArticlesController::class, 'create']);
    $r->addRoute('POST', '/articles', [ArticlesController::class, 'store']);

    $r->addRoute('POST', '/articles/{id:\d+}/delete', [ArticlesController::class, 'delete']);

    $r->addRoute('GET', '/articles/{id:\d+}/edit', [ArticlesController::class, 'edit']);
    $r->addRoute('POST', '/articles/{id:\d+}', [ArticlesController::class, 'update']);
});

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
        $handler = $routeInfo[1];   // routeInfo ir array, kas ir iekavās aiz addRoute(0=>'GET', 1=>'/articles', 2=>[ArticlesController::class, 'index'])
        $controller = $handler[0];
        $method = $handler[1];
        $vars = $routeInfo[2];

        /** @var View $response */ //lai var getPath un getVariables pasaukt
        $response = (new $controller)->$method($vars);

        $loader = new FilesystemLoader('app/Views'); //filename path
        $twig = new Environment($loader); //noņēmām kašošanu

        if ($response instanceof View) {
            echo $twig->render($response->getPath() . '.html', $response->getVariables());
        }
        if ($response instanceof Redirect) {
            header('Location: ' . $response->getLocation());
            exit;
        }
        break;
}

