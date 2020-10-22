<?php
/**
 * Created by PhpStorm.
 * Author: xinu x-php@outlook.com
 * Coding Standard: PSR2
 * DateTime: 2020-10-20 15:44
 * 后端路由文件
 */

use App\Http\Controllers\Admin\Auth\AuthController;
use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\Auth\RuleContrller;
use App\Http\Middleware\AuthXm;
use Illuminate\Support\Facades\Route;


// Auth授权相关内容
Route::name('auth_system.')->middleware(AuthXm::class)->group(static function () {
    Route::post('/auths', AuthController::class . '@index');
    Route::get('/userinfo', AuthController::class . '@index');

    // 规则的增删改查
    Route::apiResource('/rules', RuleContrller::class);
    // 获取规则的父级关系
    Route::get('/rule/options', RuleContrller::class . '@pidTree');

    // 登出
    Route::delete('/logout', LoginController::class . '@logout')->name('logout');
});

// 无需登录鉴权的接口
Route::name('not_need_login.')->group(static function () {
    // 登录
    Route::post('/login', LoginController::class . '@index')->name('login');
});
