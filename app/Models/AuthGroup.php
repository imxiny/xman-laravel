<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AuthGroup extends Model
{
    protected $primaryKey = 'group_id';
    use SoftDeletes;
    protected $guarded = [];
    protected $hidden = ['deleted_at'];

    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    /**
     * Desc: 角色组拥有的权限
     * Author: xinu
     * Time: 2020-10-22 22:22
     * @return BelongsToMany
     */
    public function rules(): BelongsToMany
    {
        return $this->belongsToMany(AuthRule::class, 'auth_group_rules', 'group_id', 'rule_id');
    }

    /**
     * Desc: 角色组被授权的用户
     * Author: xinu
     * Time: 2020-10-22 22:42
     * @return BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(AdminUser::class, 'auth_group_access', 'group_id', 'uid');
    }
}
