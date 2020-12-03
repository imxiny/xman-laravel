<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Auth\AuthGroupRequest;
use App\Models\AuthGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GroupController extends Controller
{
    /**
     * Desc: 角色列表
     * Author: xinu
     * Time: 2020-10-22 22:11
     * @return JsonResponse|object
     */
    public function index()
    {
        $groups = AuthGroup::all(['group_id', 'title', 'desc', 'pid', 'created_at', 'is_used']);
        $titles = $groups->pluck('title', 'group_id')->toArray();
        foreach ($groups as $group) {
            $group->parent_title = $titles[$group->pid] ?? '';
        }
        return xm_response($groups);
    }

    public function show()
    {
        return '';
    }

    public function store(AuthGroupRequest $request)
    {
        $input = $request->validated();
        $ruleIds = $input['rules'];
        unset($input['rules']);
        $group = DB::transaction(static function () use ($input, $ruleIds) {
            $group = AuthGroup::query()->create($input);
            $group->rules()->attach($ruleIds);
            return $group;
        });
        return xm_response($group, '角色创建成功', 201);
    }

    public function destroy(AuthGroup $group)
    {
        DB::transaction(static function () use ($group) {
            // delete user relation
            $group->users()->detach();
            // delete group rules
            $group->rules()->detach();
            $group->newQuery()->where('pid', $group->group_id)->update(['pid' => 0]);
            // delet group
            $group->delete();
        });
        return xm_res(204, '角色组删除成功');
    }

    public function update(AuthGroup $group, AuthGroupRequest $request)
    {
        $input = $request->validated();
        $group = DB::transaction(static function () use ($input, $group) {
            if (isset($input['rules'])) {
                $group->rules()->sync($input['rules']);
                unset($input['rules']);
            }
            $group->update($input);
            return $group;
        });
        return xm_response($group->fresh(), '', 201);
    }
}
