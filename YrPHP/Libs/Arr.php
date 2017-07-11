<?php
/**
 * Created by YrPHP.
 * User: Kwin
 * QQ:284843370
 * Email:kwinwong@hotmail.com
 * GitHub:https://github.com/kwinH/YrPHP
 */
namespace YrPHP;

class Arr
{
    /**
     * 不区分大小写的in_array实现
     * @param string $value
     * @param array $array
     * @return bool
     */
    public static function inIArray($value = '', $array = [])
    {
        return in_array(strtolower($value), array_map('strtolower', $array));
    }

    /**
     * 在数组中搜索给定的值（不区分大小写），如果成功则返回相应的键名
     * @param $needle
     * @param $haystack
     * @param bool $strict
     * @return mixed
     */
    public static function arrayISearch($needle, $haystack, $strict = false)
    {
        return array_search(strtolower($needle), array_map('strtolower', $haystack), $strict); // $key = 2;
    }


    /**
     * 不区分key值大小写获取数组中的值
     * @param array $arr
     * @param string $key
     * @return mixed
     */
    public static function arrayIGet(array $arr = [], $key = '')
    {
        if (isset($arr[$key])) {
            return $arr[$key];
        }

        $arr = array_change_key_case($arr, CASE_LOWER);
        $key = strtolower($key);

        return isset($arr[$key]) ? $arr[$key] : null;
    }

    /**
     * 多维数组转一维数组
     * @param array $multi
     * @return array
     */
    public static function arrToOne(array $multi = [])
    {
        $arr = array();
        foreach ($multi as $val) {
            if (is_array($val)) {
                $arr = array_merge($arr, static::arrToOne($val));
            } else {
                $arr[] = $val;
            }
        }
        return $arr;
    }


    /**
     *  判断是不是索引数组
     * @param array $array
     * @return bool true ? 索引数组 : 不是索引数组
     */

    public static function isAssoc(array  $array = [])
    {
        if (is_array($array)) {
            $keys = array_keys($array);
            return $keys !== array_keys($keys);
        }
        return false;
    }


    /**
     * 使用“点”符号从数组中获取一个项。
     * @param array $arr
     * @param string $key
     * @param null $default
     * @return mixed
     */
    public static function get(array $arr = [], $key = '', $default = null)
    {
        if (!$key) {
            return $arr;
        }

        if (isset($arr[$key])) {
            return $arr[$key];
        }

        foreach (explode('.', $key) as $v) {
            if (!isset($arr[$v])) {
                return $default;
            }
            $arr = $arr[$v];
        }
        return $arr;
    }

    /**
     * 弹出指定key 并把值返回
     * @param array $arr
     * @param string $key
     * @return bool|mixed
     */
    public static function pop(array &$arr = [], $key = null, $default = null)
    {
        if (is_null($key)) {
            return array_pop($arr);
        }

        if (isset($arr[$key])) {
            $data = $arr[$key];
            unset($arr[$key]);
            return $data;
        }

        return $default;
    }


    /**
     * 返回数组中指定的数组项
     * @param array $arr 指定数组
     * @param array $onlyKey 可以为多个参数或则单个数组格式
     * @return array
     */
    public static function only(array &$arr = [], $onlyKey = array())
    {
        if (func_num_args() > 2) {
            $onlyKey = func_get_args();
            array_pop($onlyKey);
        }

        $onlyKey = array_flip($onlyKey);
        return array_intersect_key($arr, $onlyKey);
    }


    /**
     * 过滤数组中指定的数组项,并返回
     * @param array $arr 指定数组
     * @param array $exceptKey 可以为多个参数或则单个数组格式
     * @return array
     */
    public static function except(array &$arr = [], $exceptKey = array())
    {
        if (func_num_args() > 2) {
            $exceptKey = func_get_args();
            array_pop($exceptKey);
        }

        $exceptKey = array_flip($exceptKey);
        return array_diff_key($arr, $exceptKey);
    }


}