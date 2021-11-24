<?php

declare(strict_types=1);

namespace App\Middleware\Auth;

use App\Constants\ResponseCode;
use App\Exception\WsException;
use App\Service\UserService;
use Firebase\JWT\JWT;
use Hyperf\Di\Annotation\Inject;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class JwtMiddleware implements MiddlewareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @Inject
     * @var UserService
     */
    protected $userService;

    /**
     * @Inject
     * @var JWT
     */
    protected $jwt;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $token = $request->getHeader('Token');
        $token = isset($token[0]) ? $token[0] : null;
        $key = config('app.jwt.key');
        if(empty($token) || (!$jwtData = $this->jwt::decode($token, $key, ['HS256']))) {
            throw new WsException(ResponseCode::LOGIN_ERROR, '未登录');
        }
        $userId = $jwtData->data->uid ?? null;
        $userType = $jwtData->data->type ?? 'user';
        if($userType == 'admin') {
            $userInfo = $this->userService->getAdminInfoById((int)$userId);
        }else{
            $userInfo = $this->userService->getInfoById((int)$userId);
        }
        if(!$userInfo) {
            throw new WsException(ResponseCode::LOGIN_ERROR, '未登录'.$userId);
        }
        \Hyperf\WebSocketServer\Context::set('user', $userInfo);
        \Hyperf\WebSocketServer\Context::set('user_type', $userType);
        return $handler->handle($request);
    }
}