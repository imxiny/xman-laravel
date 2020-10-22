<?php

namespace App\Http\Middleware;

use App\Http\Services\LoginService;
use Closure;
use Illuminate\Support\Facades\Cache;

class AuthXm
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     * @throws \App\Exceptions\CustomException
     */
    public function handle($request, Closure $next)
    {
        // 登录校验
        $token = $request->header('X-Token');
        if (empty($token)) {
            xm_abort('未登录，需要登录', 401);
        }
        $uid = LoginService::getUserByToken($token);
        if (!LoginService::checkToken($token, $uid)) {
            xm_abort('登录已失效，需要重新登录', 401);
        }
        LoginService::setTokenCache($uid, $token); // 更新有效期
        // 权限校验
        // TODO
        $request->offsetSet('uuuid', $uid);
        return $next($request);
    }
}
