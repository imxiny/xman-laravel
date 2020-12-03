<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Auth\AuthRuleRequest;
use App\Models\AuthRule;
use App\Tools\DataTool;
use Illuminate\Support\Facades\DB;


class RuleContrller extends Controller
{
    /**
     * Desc: 规则列表
     * Author: xinu
     * Time: 2020-10-20 21:56
     * @param AuthRuleRequest $request
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

    /**
     * Desc: 获取树形结构 授权使用
     * Author: xinu
     * Time: 2020-10-22 23:35
     * @return \Illuminate\Http\JsonResponse|object
     */
    public function tree()
    {
        $data = xm_recursion_query('auth_rules', ['rule_id', 'title'], 'pid', 'rule_id', 'children', 0, DB::raw('deleted_at'), 'has_children');
        return xm_response($data);
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
        return xm_response(AuthRule::query()->create($arr)->fresh(), '规则创建成功', 201);
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
        array_unshift($data, ['rule_id' => 0, '__title' => '无', 'title' => '无']);
        return xm_response($data);
    }



    /**
     * Desc: 删除规则，以及对应的子规则
     * Author: xinu
     * Time: 2020-10-21 22:22
     * @param AuthRule $rule
     * @return \Illuminate\Http\JsonResponse|object
     * @throws \Exception
     */
    public function destroy(AuthRule $rule)
    {
        $children = xm_query_children('auth_rules', 'pid', 'rule_id', $rule->rule_id, 'deleted_at is null', false);
        DB::transaction(static function () use ($rule, $children) {
            DB::table('auth_group_rules')->whereIn('rule_id', array_merge($children, [$rule->rule_id]))->delete();
            $rule->delete();
            if ($children) {
                $rule->newQuery()->whereIn('rule_id', $children)->delete();
            }
        });
        return xm_res(204, '删除成功');
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
        return xm_response($rule->fresh(), '规则更新成功', 201);
    }
}
