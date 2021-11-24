<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

use App\Middleware\Auth\JwtMiddleware;
use App\Middleware\Auth\WsJwtMiddleware;
use Hyperf\HttpServer\Router\Router;

$authMiddleWare = [
    JwtMiddleware::class,
];

$wsAuthMiddleWare = [
    WsJwtMiddleware::class,
];


Router::addRoute(['POST', 'GET'], '/login', 'App\Controller\UserController@login');
Router::addRoute(['POST', 'GET'], '/admin-login', 'App\Controller\AdminController@login');

Router::get('/favicon.ico', function () {
    return '';
});

Router::addGroup('', function ()
{
    Router::addRoute(['GET', 'POST', 'HEAD'], '/', 'App\Controller\IndexController@index');
    Router::addRoute(['GET'], '/user/get-session', 'App\Controller\UserController@getSession');
    Router::addRoute(['GET'], '/user/get-info', 'App\Controller\UserController@getInfo');

}, ['middleware' => $authMiddleWare]);


Router::addServer('ws', function () use ($wsAuthMiddleWare) {
    Router::addGroup('', function ()
    {
        Router::get('/', 'App\Controller\WebSocketController');
    }, ['middleware' => $wsAuthMiddleWare]);

});

