<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AuthRuleResource extends JsonResource
{
    public function toArray($request)
    {
        $actionName = $request->route()->getActionMethod();
        $reqActionType = strtolower($request->getMethod());
        $re = $reqActionType . '_' . $actionName;
        return $this->$re($request);
    }

    public function get_index($request)
    {
        return [
            'rule_id' => $this->rule_id,
            'rule' => $this->rule,
            'title' =>  $this->title,
            'is_used' => $this->is_used,
            'rule_type' => $this->rule_type,
            'pid' => $this->pid,
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }

    public function __call($methodName, $args)
    {
        return parent::toArray(...$args);
    }
}
