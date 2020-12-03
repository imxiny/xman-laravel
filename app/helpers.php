<?php
/**
 * Created by PhpStorm.
 * Author: xinu x-php@outlook.com
 * Coding Standard: PSR2
 * DateTime: 2020-10-21 8:57
 */

use App\Exceptions\CustomException;
use \Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

if (!function_exists('_sql')) {
    function _sql($log = false)
    {
        DB::listen(function ($query) use ($log) {
            $bindings = $query->bindings;
            $sql = $query->sql;
            foreach ($bindings as $replace) {
                $value = is_numeric($replace) ? $replace : "'" . $replace . "'";
                $sql = preg_replace('/\?/', $value, $sql, 1);
            }
            $log ? Log::info($sql) : dump($sql);
        });
    }
}

if (!function_exists('xm_abort')) {
    /**
     * Desc: 抛出自定义异常
     * Author: xinu
     * Time: 2020-10-21 9:35
     * @param $message
     * @param int $code
     * @param null $data
     * @throws CustomException
     */
    function xm_abort($message, $code = 404, $data = null)
    {
        throw new CustomException($message, $code, $data);
    }
}

if (!function_exists('xm_response')) {
    /**
     * Desc: 抛出自定义异常
     * Author: xinu
     * Time: 2020-10-21 9:35
     * @param array $data
     * @param string $message
     * @param int $code
     * @return \Illuminate\Http\JsonResponse|object
     */
    function xm_response($data = [], $message = '', int $code = 200)
    {
        $res = [
            'message' => $message
        ];
        isset($data) && $res['data'] = $data;
        return response()->json($res)->setStatusCode($code);
    }
}

if (!function_exists('xm_recursion_query')) {
    /**
     * Desc: 获取无限分类父子结构数据
     * Author: xinu
     * Time: 2020-10-21 23:09
     * @param $table
     * @param string $column
     * @param string $pidColumn
     * @param string $id
     * @param string $children
     * @param int $start
     * @param array $where
     * @return array
     */
    function xm_recursion_query($table, $column = '*', $pidColumn = 'pid', $id = 'id', $children = 'children', $start = 0, $where = [], $hasChildren = '')
    {
        $query = DB::table($table);
        if ($where) {
            $query->where($where);
        }
        $pids = $query->distinct()->pluck($pidColumn)->toArray();
        function query($table, $column = '*', $pidColumn = 'pid', $id = 'id', $children = 'children', $start = 0, $where = [], $pids = [], $hasChildren = '')
        {
            $res = [];
            $query = DB::table($table);
            if ($where) {
                $query->where($where);
            }
            if ($column !== '*') {
                $query->select($column);
            }
            if (!in_array($start, $pids, true)) {
                return $res;
            }
            $list = $query->where($pidColumn, $start)->get();
            if ($list->isNotEmpty()) {
                foreach ($list as $k => $item) {
                    $res[$k] = (array)$item;
                    $t = query($table, $column, $pidColumn, $id, $children, $item->{$id}, $where, $pids);
                    if ($hasChildren) {
                        $res[$k][$hasChildren] = false;
                    }
                    if ($t) {
                        $res[$k][$children] = $t;
                        if ($hasChildren) {
                            $res[$k][$hasChildren] = true;
                        }
                    }
                }
            }
            return $res;
        }

        return query($table, $column, $pidColumn, $id, $children, $start, $where, $pids, $hasChildren);
    }
}

if (!function_exists('xm_query_children')) {
    /**
     * Desc: 无限级结构中 根据父级id查所有孩子
     * Author: xinu
     * Time: 2020-10-22 20:20
     * @param string $table 表
     * @param string $parentField
     * @param string $childField
     * @param int $id
     * @param string $extraWhere 额外的条件
     * @param bool $containSelf
     * @return array
     */
    function xm_query_children(string $table, string $parentField, string $childField, int $id, string $extraWhere = '', bool $containSelf = true)
    {
        $table = config('database.connections.mysql.prefix', '') . $table;
        $sql = "SELECT" . " u2.`{$childField}`
FROM (
SELECT `{$childField}`,
@ids                           AS p_ids,
(SELECT @ids := GROUP_CONCAT(`{$childField}`)
FROM `{$table}`
WHERE FIND_IN_SET(`{$parentField}`, @ids)) AS c_ids,
@l := @l + 1                   AS LEVEL
FROM `{$table}`,
(SELECT @ids := {$id}, @l := 0) b
WHERE @ids IS NOT NULL
) u1
JOIN `{$table}` u2
ON FIND_IN_SET(u2.`{$childField}`, u1.p_ids)";
        if (!$containSelf) {
            $sql .= " where u2.`{$childField}` != {$id}";
        }
        if ($extraWhere) {
            if (false !== strpos($sql, 'where')) {
                $sql .= " and " . $extraWhere;
            } else {
                $sql .= ' where ' . $extraWhere;
            }
        }
        $list = DB::select($sql);
        return array_column($list, $childField);
    }
}

if (!function_exists('xm_res')) {
    /**
     * Desc: 抛出自定义异常
     * Author: xinu
     * Time: 2020-10-21 9:35
     * @param array $data
     * @param string $message
     * @param int $code
     * @return \Illuminate\Http\JsonResponse|object
     */
    function xm_res(int $code, string $message = '')
    {
        return xm_response(null, $message, $code);
    }
}
