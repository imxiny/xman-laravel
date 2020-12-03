<?php

namespace App\Http\Requests\Admin\Auth;

use App\Models\AuthRule;
use Illuminate\Foundation\Http\FormRequest;

class AuthGroupRequest extends FormRequest
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
     * @throws \App\Exceptions\CustomException
     */
    public function rules()
    {
        $route = $this->route();
        if (null === $route) {
            xm_abort('路由错误', 404);
        }
        $actionName = strtolower($this->getMethod()) . '_' . $route->getActionMethod();
        return $this->{$actionName}();
    }

    public function post_store()
    {
        return [
            'title' => 'required|string|min:2',
            'is_used' => 'required|in:0,1',
            'desc' => 'required|string',
            'rules' => [
                'required',
                static function ($attr, $value, $fail) {
                    if (!is_array($value)) {
                        return $fail('非法的规则');
                    }
                    if (count($value) !== AuthRule::query()->whereIn('rule_id', $value)->count()) {
                        return $fail('非法的规则');
                    }
                }
            ]
        ];
    }

    public function put_update()
    {
        return $this->post_store();
    }

    public function patch_update()
    {
        $all = $this->all();
        if (empty($all)) {
            return xm_abort('需要携带要修改的属性', 422);
        }
        $rules = [];
        $allRules = $this->put_update();
        foreach ($all as $k => $v) {
            if (array_key_exists($k, $allRules)) {
                $rules[$k] = $allRules[$k];
            }
        }
        if (empty($rules)) {
            return xm_abort('需要携带要修改的属性', 422);
        }
        return $rules;
    }

    public function get_index()
    {
        return [];
    }

    public function __call($name, $args)
    {
        return [];
    }
}
