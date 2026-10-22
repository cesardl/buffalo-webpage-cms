<?php

declare(strict_types=1);

use Buffalo\Config;
use Buffalo\Container;
use Buffalo\Http\Request;
use Buffalo\Http\RouteTable;
use Buffalo\Http\Router;

require dirname(__DIR__) . '/vendor/autoload.php';

$container = new Container(
    Config::fromEnvironment(),
    dirname(__DIR__) . '/templates',
);

$router = new Router(
    routes: RouteTable::all(),
    session: $container->session(),
    resolve: $container->controller(...),
    loginPath: RouteTable::LOGIN,
);

$router->dispatch(Request::fromGlobals())->send();
