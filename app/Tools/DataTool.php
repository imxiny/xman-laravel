<?php
/**
 * Created by PhpStorm.
 * Author: xinu x-php@outlook.com
 * Coding Standard: PSR2
 * DateTime: 2020-10-22 10:48
 */


namespace App\Tools;

final class DataTool
{

    /**
     * Desc: 根据pid与id的关系返无限树形结构数据
     * Author: xinu
     * Time: 2020-10-22 17:43
     * @param array $data
     * @param string $parentField
     * @param string $childField
     * @param string $title
     * @return array
     */
    public static function tree(array $data, $parentField = 'pid', $childField = 'id', $title = 'title'): array
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

    /**
     * Desc: 返回以指定列做key的关联数组
     * Author: xinu
     * Time: 2020-10-22 17:43
     * @param array $array
     * @param $field
     * @return array
     */
    public static function keyBy(array $array, $field): array
    {
        $res = [];
        foreach ($array as $v) {
            $res[$v[$field]] = $v;
        }
        return $res;
    }
}
