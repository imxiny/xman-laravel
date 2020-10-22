<?php

namespace App\Http\Services;

use App\Models\AdminUser;
use Exception;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;

class LoginService
{
    public const PREFIX = 'admin_';
    protected static $key;

    public static function getKey($userId): string
    {
        return self::PREFIX . $userId;
    }

    public static function logout($uid)
    {
        return Cache::forget(self::getKey($uid));
    }

    public static function getTokenCache($uid)
    {
        return Cache::get(self::getKey($uid));
    }

    public static function checkToken($token, $uid): bool
    {
        return $token === self::getTokenCache($uid);
    }

    public static function setTokenCache($uid, $value)
    {
        return Cache::put(self::getKey($uid), $value, config('xm.login_expried'));
    }

    /**
     * Desc: 校验帐号密码 如果通过则下发jwt
     * Author: xinu
     * Time: 2020-10-21 9:42
     * @param $username
     * @param $password
     * @return array
     * @throws \App\Exceptions\CustomException
     */
    public function verify($username, $password): array
    {
        $userInfo = AdminUser::query()->where('username', $username)->first();
        if (empty($userInfo)) {
            xm_abort('帐号不存在');
        }
        if (!hash_equals(self::password($password), $userInfo->password)) {
            xm_abort('密码错误');
        }
        return [
            'token' => self::encode(
                [
                    config('xm.jwt_id') => $userInfo->id,
                    'rand_str' => (config('app.env') === 'local' ? '' : bin2hex(random_bytes(10)))
                ],
                config('xm.jwt_key')
            ),
            'uid' => $userInfo->id];
    }

    /**
     * Desc: token中获取uid
     * Author: xinu
     * Time: 2020-10-21 11:34
     * @param $token
     * @return false|mixed
     */
    public static function getUserByToken($token)
    {
        try {
            $info = self::decode($token, config('xm.jwt_key'));
            return $info[config('xm.jwt_id')];
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Desc: jwt加密
     * Author: xinu
     * Time: 2020-10-21 9:20
     * @param $payload
     * @param string $key
     * @param string $shaTyp
     * @return string
     */
    public static function encode($payload, $key = '', $shaTyp = 'HS256'): string
    {
        self::$key = $key ?: config('');
        if (empty(self::$key)) {
            throw new InvalidArgumentException('jwt_key不可为空');
        }
        return JWT::encode($payload, self::$key, $shaTyp);
    }

    /**
     * Desc: jwt 解密
     * Author: xinu
     * Time: 2020-10-21 9:21
     * @param $jwt
     * @param string $key
     * @param string[] $shaTyp
     * @return array
     */
    public static function decode($jwt, $key = '', $shaTyp = ['HS256']): array
    {
        self::$key = $key ?: config('xm.jwt_key');
        if (empty(self::$key)) {
            throw new InvalidArgumentException('jwt_key不可为空');
        }
        return (array)JWT::decode($jwt, self::$key, $shaTyp);
    }

    /**
     * Desc: 密码加密
     * Author: xinu
     * Time: 2020-10-21 9:39
     * @param $pass
     * @return string
     */
    public static function password($pass): string
    {
        return md5(substr(config('app.key'), 10, 8) . md5($pass));
    }
}

