<?php
/**
 * Created by PhpStorm.
 * Author: xinu x-php@outlook.com
 * Coding Standard: PSR2
 * DateTime: 2020-10-22 10:48
 */


namespace App\Tool;

final class DataTool
{
    /**
     * 返回多层栏目
     * @param $data array 操作的数组
     * @param int $pid 一级PID的值
     * @param string $html 栏目名称前缀
     * @param string $fieldPri 唯一键名，如果是表则是表的主键
     * @param string $fieldPid 父ID键名
     * @param int $level 不需要传参数（执行时调用）
     * @return array
     */
    public static function channelLevel(array $data, $pid = 0, $html = "&nbsp;", $fieldPri = 'cid', $fieldPid = 'pid', $level = 1): array
    {
        if (empty($data)) {
            return array();
        }
        $arr = array();
        foreach ($data as $v) {
            if ($v[$fieldPid] === $pid) {
                $arr[$v[$fieldPri]] = $v;
                $arr[$v[$fieldPri]]['_level'] = $level;
                $arr[$v[$fieldPri]]['_html'] = str_repeat($html, $level - 1);
                $arr[$v[$fieldPri]]["_data"] = self::channelLevel($data, $v[$fieldPri], $html, $fieldPri, $fieldPid, $level + 1);
            }
        }
        return $arr;
    }

    /**
     * 获得所有子栏目
     * @param $data array 栏目数据
     * @param int $pid 操作的栏目
     * @param string $html 栏目名前字符
     * @param string $fieldPri 表主键
     * @param string $fieldPid 父id
     * @param int $level 等级
     * @return array
     */
    public static function channelList(array $data, $pid = 0, $html = "&nbsp;", $fieldPri = 'cid', $fieldPid = 'pid', $level = 1): array
    {
        $data = self::_channelList($data, $pid, $html, $fieldPri, $fieldPid, $level);
        if (empty($data)) {
            return $data;
        }
        foreach ($data as $n => $m) {
            if ($m['_level'] === 1) {
                continue;
            }
            $data[$n]['_first'] = false;
            $data[$n]['_end'] = false;
            if (!isset($data[$n - 1]) || $data[$n - 1]['_level'] !== $m['_level']) {
                $data[$n]['_first'] = true;
            }
            if (isset($data[$n + 1]) && $data[$n]['_level'] > $data[$n + 1]['_level']) {
                $data[$n]['_end'] = true;
            }
        }
        //更新key为栏目主键
        $category = array();
        foreach ($data as $d) {
            $category[$d[$fieldPri]] = $d;
        }
        //var_dump($category);die;
        return $category;
    }

    //只供channelList方法使用
    private static function _channelList($data, $pid = 0, $html = "&nbsp;", $fieldPri = 'cid', $fieldPid = 'pid', $level = 1): array
    {
        if (empty($data)) {
            return array();
        }
        $arr = array();
        foreach ($data as $v) {
            $id = $v[$fieldPri];
            if ($v[$fieldPid] === $pid) {
                $v['_level'] = $level;
                $v['_html'] = str_repeat($html, $level - 1);
                $arr[] = $v;
                $tmp = self::_channelList($data, $id, $html, $fieldPri, $fieldPid, $level + 1);
                $arr = array_merge($arr, $tmp);
            }
        }
        return $arr;
    }

    /**
     * 获得树状数据
     * @param $data array 数据
     * @param $title string 字段名
     * @param string $fieldPri 主键id
     * @param string $fieldPid 父id
     * @return array
     */
    public static function tree(array $data, string $title, $fieldPri = 'cid', $fieldPid = 'pid'): array
    {
        if (!is_array($data) || empty($data)) {
            return array();
        }
        $arr = self::channelList($data, 0, '', $fieldPri, $fieldPid);
        foreach ($arr as $k => $v) {
            $str = "";
            if ($v['_level'] > 2) {
                for ($i = 1; $i < $v['_level'] - 1; $i++) {
                    $str .= "　│";
                }
            }
            if ($v['_level'] !== 1) {
                $t = $title ? $v[$title] : "";
                if (isset($arr[$k + 1]) && $arr[$k + 1]['_level'] >= $arr[$k]['_level']) {
                    $arr[$k]['_name'] = $str . "　├─ " . $v['_html'] . $t;
                } else {
                    $arr[$k]['_name'] = $str . "　└─ " . $v['_html'] . $t;
                }
            } else {
                $arr[$k]['_name'] = $v[$title];
            }
        }
        //设置主键为$fieldPri
        $data = array();
        foreach ($arr as $d) {
            $data[$d[$fieldPri]] = $d;
        }
        return $data;
    }

    /**
     * 获得所有父级栏目
     * @param $data array 栏目数据
     * @param $sid int 子栏目
     * @param string $fieldPri 唯一键名，如果是表则是表的主键
     * @param string $fieldPid 父ID键名
     * @return array
     */
    public static function parentChannel(array $data, int $sid, $fieldPri = 'cid', $fieldPid = 'pid'): ?array
    {
        if ($data === null) {
            return null;
        }

        $arr = array();
        foreach ($data as $v) {
            if ($v[$fieldPri] === $sid) {
                $arr[] = $v;
                $_n = self::parentChannel($data, $v[$fieldPid], $fieldPri, $fieldPid);
                if (!empty($_n)) {
                    $arr = array_merge($arr, $_n);
                }
            }
        }
        return $arr;
    }

    /**
     * 判断$s_cid是否是$d_cid的子栏目
     * @param $data array 栏目数据
     * @param $sid int 子栏目id
     * @param $pid int 父栏目id
     * @param string $fieldPri 主键
     * @param string $fieldPid 父id字段
     * @return bool
     */
    public static function isChild(array $data, int $sid, int $pid, $fieldPri = 'cid', $fieldPid = 'pid'): bool
    {
        $_data = self::channelList($data, $pid, '', $fieldPri, $fieldPid);
        foreach ($_data as $c) {
            //目标栏目为源栏目的子栏目
            if ($c[$fieldPri] === $sid) {
                return true;
            }
        }
        return false;
    }

    /**
     * 检测是不否有子栏目
     * @param $data array 栏目数据
     * @param $cid int 要判断的栏目cid
     * @param string $fieldPid 父id表字段名
     * @return bool
     */
    public static function hasChild(array $data, int $cid, $fieldPid = 'pid'): bool
    {
        foreach ($data as $d) {
            if ($d[$fieldPid] === $cid) {
                return true;
            }
        }
        return false;
    }

    /**
     * 递归实现迪卡尔乘积
     * @param $arr array 操作的数组
     * @param array $tmp
     * @return array
     */
    public static function descarte(array $arr, $tmp = array()): array
    {
        static $n_arr = array();
        $array = $arr;
        foreach (array_shift($array) as $v) {
            $tmp[] = $v;
            if ($arr) {
                self::descarte($arr, $tmp);
            } else {
                $n_arr[] = $tmp;
            }
            array_pop($tmp);
        }
        return $n_arr;
    }

}
