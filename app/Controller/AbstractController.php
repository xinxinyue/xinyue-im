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
namespace App\Controller;

use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;

abstract class AbstractController
{
    /**
     * @Inject
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @Inject
     * @var RequestInterface
     */
    protected $request;

    /**
     * @Inject
     * @var ResponseInterface
     */
    protected $response;

    /**
     * 统一响应
     * @param int $code
     * @param null $msg
     * @param array $data
     * @return \Psr\Http\Message\ResponseInterface
     * @author: zhangkaiyue
     * Time: 2021/4/27   16:31
     */
    protected function generateResponse($code = 1000, $msg = null, $data = []){
        $response = ['code' => $code, 'msg' => $msg];
        if(!empty($data)){
            $response['data'] = $data;
        }
        return $this->response->json($response);
    }
}
