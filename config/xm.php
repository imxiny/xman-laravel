<?php
/**
 * Created by PhpStorm.
 * Author: xinu x-php@outlook.com
 * Coding Standard: PSR2
 * DateTime: 2020-10-21 9:15
 */

return [
    // jwt 加密使用的key
    'jwt_key' => env('APP_KEY', 'xman'),
    // jwt payload中存放id的字段
    'jwt_id' => 'admin_id',
    'login_expried' => 3600, // 登录有效时间 s
];
