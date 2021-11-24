<?php

declare(strict_types=1);

namespace App\Constants;

use Hyperf\Constants\AbstractConstants;
use Hyperf\Constants\Annotation\Constants;

/**
 * @Constants
 */
class ResponseCode extends AbstractConstants
{
    /**
     * @Message("服务器内部错误！")
     */
    const SERVER_ERROR = 500;

    /**
     * @Message("成功");
     */
    const SUCCESS = 1000;

    /**
     * @Message("验证失败");
     */
    const VALIDATE_ERROR = 1001;

    /**
     * @Message("未登录");
     */
    const LOGIN_ERROR = 1002;
}
