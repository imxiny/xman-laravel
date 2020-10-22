<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\AuthRuleRequest;
use App\Models\AuthRule;
use App\Tools\DataTool;


class RuleContrller extends Controller
{
    /**
     * Desc: 规则列表
     * Author: xinu
     * Time: 2020-10-20 21:56
     * @return string
     */
    public function index(AuthRuleRequest $request)
    {
        $key = $request->get('keyword', null);

        $query = AuthRule::query();
        if ($key) {
            $query->where('rule', 'like', "%{$key}%")->orWhere('title', 'like', "%{$key}%");
        }
        $data = DataTool::tree($query->get()->toArray(), 'pid', 'rule_id', 'title');
        return xm_response($data);
    }

    public function show()
    {
        return [];
    }

    /**
     * Desc: 新增规则
     * Author: xinu
     * Time: 2020-10-21 14:50
     * @param AuthRuleRequest $request
     */
    public function store(AuthRuleRequest $request)
    {
        $arr = $request->validated();
        return xm_response(AuthRule::query()->create($arr)->fresh(), '', 201);
    }


    /**
     * Desc: 规则的父子关系结构
     * Author: xinu
     * Time: 2020-10-21 22:23
     */
    public function pidTree()
    {
        $nodes = AuthRule::query()->get(['rule_id', 'pid', 'title'])->toArray();
        $data = DataTool::tree($nodes, 'pid', 'rule_id', 'title');
        array_unshift($data, ['rule_id' => 0, '__title' => '无']);
        return xm_response($data);
    }



    /**
     * Desc: 删除规则
     * Author: xinu
     * Time: 2020-10-21 22:22
     * @param AuthRule $rule
     * @return \Illuminate\Http\JsonResponse|object
     * @throws \Exception
     */
    public function destroy(AuthRule $rule)
    {
        $rule->delete();
        return xm_res(204);
    }

    /**
     * Desc: 更新规则 及 部分更新
     * Author: xinu
     * Time: 2020-10-21 16:17
     * @param AuthRuleRequest $request
     * @param AuthRule $rule
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function update(AuthRule $rule, AuthRuleRequest $request)
    {
        $arr = $request->validated();
        $rule->update($arr);
        return xm_response($rule->fresh(), '', 201);
    }
}
