<?php
session_start();

use App\Controllers\ArticleCommentsController;
use App\Controllers\ArticlesController;
use App\Controllers\FriendsController;
use App\Controllers\UsersController;
use App\Controllers\WelcomeController;
use App\Redirect;
use App\Views\View;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

require_once 'vendor/autoload.php';

$dispatcher = FastRoute\simpleDispatcher(function (FastRoute\RouteCollector $r) {  //'GET', '/articles' != 'POST', '/articles',
    //Define routes adn what to do next:
    $r->addRoute('GET', '/', [WelcomeController::class, 'opening']);
    $r->addRoute('GET', '/welcome', [WelcomeController::class, 'welcome']);

    // Users
    $r->addRoute('GET', '/users/signup', [UsersController::class, 'signup']);
    $r->addRoute('POST', '/users', [UsersController::class, 'register']);

    $r->addRoute('GET', '/users/login', [UsersController::class, 'login']);
    $r->addRoute('POST', '/users/login', [UsersController::class, 'enter']);

    $r->addRoute('GET', '/users/logout', [UsersController::class, 'logout']);

    $r->addRoute('GET', '/users', [UsersController::class, 'index']);
    $r->addRoute('GET', '/users/{id:\d+}', [UsersController::class, 'show']);

    $r->addRoute('POST', '/users/{id:\d+}/invite', [FriendsController::class, 'invite']);

    $r->addRoute('GET', '/invites', [FriendsController::class, 'invites']);
    $r->addRoute('POST', '/invites/{id:\d+}/invite', [FriendsController::class, 'accept']);

    $r->addRoute('GET', '/friends', [FriendsController::class, 'show']);

    // Articles
    $r->addRoute('GET', '/articles', [ArticlesController::class, 'index']);
    $r->addRoute('GET', '/articles/{id:\d+}', [ArticlesController::class, 'show']);

    $r->addRoute('GET', '/articles/create', [ArticlesController::class, 'create']);
    $r->addRoute('POST', '/articles', [ArticlesController::class, 'store']);

    $r->addRoute('POST', '/articles/{id:\d+}/delete', [ArticlesController::class, 'delete']);

    $r->addRoute('GET', '/articles/{id:\d+}/edit', [ArticlesController::class, 'edit']);
    $r->addRoute('POST', '/articles/{id:\d+}', [ArticlesController::class, 'update']);

    $r->addRoute('POST', '/articles/{id:\d+}/like', [ArticlesController::class, 'like']);

    $r->addRoute('POST', '/articles/{id:\d+}/comment', [ArticleCommentsController::class, 'comment']);
    $r->addRoute('POST', '/articles/{nr:\d+}/erase/{id:\d+}', [ArticleCommentsController::class, 'erase']);

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
        var_dump("404 Not Found");
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $allowedMethods = $routeInfo[1];
        var_dump("405 Method Not Allowed");
        break;
    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1];   // routeInfo array is in brackets addRoute(0=>'GET', 1=>'/articles/{id:\d+}/edit', 2=>[ArticlesController::class, 'index'])
        $controller = $handler[0];
        $method = $handler[1];
        $vars = $routeInfo[2];

        /** @var View $response */ // because of this getPath and getVariables can be called
        $response = (new $controller)->$method($vars);

        $loader = new FilesystemLoader('app/Views'); //filename path
        $twig = new Environment($loader);

        if ($response instanceof View) {
            echo $twig->render($response->getPath() . '.html', $response->getVariables());
        }
        if ($response instanceof Redirect) {
            header('Location: ' . $response->getLocation());
            exit;
        }
        break;
}

if (isset($_SESSION['inputTitle']) || isset($_SESSION['inputDescription'])) {
    unset ($_SESSION['inputTitle']);
    unset ($_SESSION['inputDescription']);
}

