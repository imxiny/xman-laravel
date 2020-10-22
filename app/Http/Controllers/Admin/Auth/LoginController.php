<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Exceptions\CustomException;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Services\LoginService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    /**
     * Desc: 登录获取授权token
     * Author: xinu
     * Time: 2020-10-21 8:50
     * @param LoginRequest $request
     * @param LoginService $service
     * @return JsonResponse|object
     * @throws CustomException
     */
    public function index(LoginRequest $request, LoginService $service)
    {
        $username = $request->get('username');
        $password = $request->get('password');
        $tokenInfo = $service->verify($username, $password);
        $service::setTokenCache($tokenInfo['uid'], $tokenInfo['token']);
        return xm_response(['token' => $tokenInfo['token']]);
    }

    /**
     * Desc: 登出 销毁token
     * Author: xinu
     * Time: 2020-10-21 13:54
     * @param Request $request
     * @return JsonResponse|object
     */
    public function logout(Request $request)
    {
        LoginService::logout($request->uuuid);
        return xm_response([], '登出成功');
    }
}
