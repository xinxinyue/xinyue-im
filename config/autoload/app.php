<?php

declare(strict_types=1);


return [
    'auth' => [
        'password_hash' => 'hyperf-sword'    //密码hash
    ],
    'jwt' => [
        'key' => 'hyperf-sword',
    ],
    'connect' => [
        'key' => 'connect',
    ],
    'bind'  => [
        'bind_type'  => 1,   //绑定客服类型 1轮训 2hash ...
        'key' => 'bind_support',
    ],
];
