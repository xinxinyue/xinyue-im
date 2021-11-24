<?php

declare(strict_types=1);

namespace App\Controller;

use App\Constants\ResponseCode;
use App\Request\UserLoginRequest;
use App\Service\UserService;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;

/**
 */
class AdminController extends AbstractController
{

    /**
     * @Inject
     * @var UserService
     */
    private $userService;

    public function login(RequestInterface $request)
    {
        $data = $this->userService->adminLogin($request->input('id'));
        return $this->generateResponse(ResponseCode::SUCCESS, '', $data);
    }

    public function register()
    {

    }
}
