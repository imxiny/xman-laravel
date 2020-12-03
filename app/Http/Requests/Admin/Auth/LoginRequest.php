<?php

namespace App\Http\Requests\Admin\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $actionName = strtolower($this->getMethod()) . '_' . $this->route()->getActionMethod();
        return $this->$actionName();
    }

    /**
     * Desc: 登录校验
     * Author: xinu
     * Time: 2020-10-21 9:45
     * @return array
     */
    public function post_index()
    {
        return [
            'username' => 'required|string',
            'password' => 'required',
        ];
    }

    public function put_update()
    {
        return [];
    }

    public function get_index()
    {
        return [];
    }

    public function _messages()
    {
        return [
            'username.required' => "帐号必填",
            'password.required' => "密码必填",
        ];
    }

    public function __call($name, $args)
    {
        return [];
    }
}
