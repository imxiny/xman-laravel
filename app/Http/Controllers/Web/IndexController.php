<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AuthRule;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    public function index()
    {
        $list = AuthRule::all(['rule_id', 'pid', 'title'])->toArray();
        dd(self::tree($list, 'pid', 'rule_id'));
    }

    public static function tree(array $data, $parentField = 'pid', $childField = 'id', $title = 'title')
    {
        // 判断是否存在 pid = 0 根节点，如果不存在根节点，无法处理
        $pids = array_unique(array_column($data, $parentField));
        $ids = array_unique(array_column($data, $childField));
        $root = array_values(array_diff($pids, $ids));
        if (empty($root)) {
            throw new \InvalidArgumentException('参数无效，无有效根节点');
        }
        // 多根节点的树形处理
        $changeLog = [];
        $newRoot = array_shift($root);
        if (count($root) > 0) {
            foreach ($data as $k => $v) {
                if (in_array($v[$parentField], $root, true)) {
                    // 记录修改 最后需要还原pid
                    $changeLog[$v[$childField]] = $v[$parentField];
                    $data[$k][$parentField] = $newRoot;
                }
            }
        }
        $displayTitle = '__title';
        $level = '__level';
        function sortData(array $data, $parentField = 'pid', $childField = 'id', $parents = [], $root = 0, $levelNum = 0, $level = '__level')
        {
            $res = [];
            foreach ($data as $k => $item) {
                $item[$level] = $levelNum;
                if ($item[$parentField] === $root) {
                    $item[$level] += 1;
                    unset($data[$k]);
                    $res[] = $item;
                    if (in_array($item[$childField], $parents, true)) {
                        $res = array_merge($res, sortData($data, $parentField, $childField, $parents, $item[$childField], $item[$level], $level));
                    }
                }
            }
            return $res;
        }

        $list = sortData($data, $parentField, $childField, $pids, $newRoot, 0, $level);
        if ($changeLog) {
            // 根据修改记录还原记录
            $list = self::keyBy($list, $childField);
            foreach ($changeLog as $id => $v) {
                $list[$id][$parentField] = $v;
            }
            $list = array_values($list);
        }
        // 拼装title
        foreach ($list as $i => $iValue) {
            $lv = $list[$i][$level];
            if ($lv === 1) {
                $list[$i][$displayTitle] = $list[$i][$title];
            } else {
                $dt = '';
                for ($j = 2; $j < $lv; $j++) {
                    $dt .= '　│';
                }
                // 如果这是最后一个数据 必然是 结束
                if (!isset($list[$i + 1])) {
                    $list[$i][$displayTitle] = $dt . '　└─ ' . $iValue[$title];
                } elseif ($lv === $list[$i + 1][$level]) {
                    // 当前level 与下条数据level一致 则顺延
                    $list[$i][$displayTitle] = $dt . '　├─ ' . $iValue[$title];
                } else {
                    $list[$i][$displayTitle] = $dt . '　└─ ' . $iValue[$title];
                }
            }
        }
        return $list;
    }

    public static function keyBy(array $array, $field): array
    {
        $res = [];
        foreach ($array as $v) {
            $res[$v[$field]] = $v;
        }
        return $res;
    }
}
