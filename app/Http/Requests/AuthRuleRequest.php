<?php

namespace App\Http\Requests;

use App\Models\AuthRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AuthRuleRequest extends FormRequest
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

    public function post_store()
    {
        return [
            'rule' => [
                'required',
                Rule::unique('auth_rules', 'rule'),
            ],
            'title' => 'required|string',
            'rule_type' => 'required|in:1,2,3,4',
            'pid' => [
                'nullable',
                static function ($attr, $value, $fail) {
                    if (((int)$value !== 0) && !AuthRule::query()->where('rule_id', $value)->exists()) {
                        return $fail("{$attr} 必须是有效的rule_id");
                    }
                }
            ]
        ];
    }

    public function put_update()
    {
        return [
            'rule' => [
                'required',
                Rule::unique('auth_rules', 'rule')->ignore($this->route()->rule, 'rule_id'),
            ],
            'title' => 'required|string',
            'is_used' => 'required|in:0,1',
            'rule_type' => 'required|in:1,2,3,4',
            'pid' => [
                'nullable',
                static function ($attr, $value, $fail) {
                    if (((int)$value !== 0) && !AuthRule::query()->where('rule_id', $value)->exists()) {
                        return $fail("{$attr} 必须是有效的rule_id");
                    }
                }
            ]
        ];
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
        return [
            'keyword' => 'nullable|string'
        ];
    }


    public function __call($name, $args)
    {
        return [];
    }
}
