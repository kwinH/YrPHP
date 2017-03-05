<?php
/**
 * Created by YrPHP.
 * User: Kwin
 * QQ: 284843370
 * Email: kwinwong@hotmail.com
 * GitHub: https://github.com/kwinH/YrPHP
 */
namespace YrPHP;

use Closure;

class Validate
{


    public static $rule = [];


    /**
     * 判断是否为空值，当数据不为空时 return true
     * @param null $value
     * @return bool
     */
    static function required($value = null)
    {
        if ($value) return true;
        return false;
    }

    /**
     * 当两个值相等时 return true
     * @param string $value
     * @param string $val
     * @return bool
     */
    static function equal($value = null, $val = null)
    {
        if (is_null($value)) return false;

        if ($value == $val) return true;

        return false;
    }

    /**
     * 当两个不值相等时 return true
     * @param string $value
     * @param string $val
     * @return bool
     */
    static function notEqual($value = null, $val = null)
    {
        if (is_null($value)) return false;

        if ($value != $val) return true;

        return false;
    }

    /**
     * 当存在指定范围时return true
     * @param string $value
     * @param array|string $range
     * @return bool
     */
    static function in($value = '', $range = '')
    {
        if (is_string($range)) {
            $range = explode(',', $range);
        } elseif (!is_array($range)) {
            return false;
        }

        if (in_array($range, $value)) return true;

        return false;
    }


    /**
     * 当不存在指定范围时return true
     * @param null $value
     * @param array|string $range
     * @return bool
     */
    static function notIn($value = '', $range = '')
    {
        if (is_string($range)) {
            $range = explode(',', $range);
        } elseif (!is_array($range)) {
            return false;
        }

        if (in_array($range, $value)) return false;

        return true;
    }


    /**
     * 当存在指定范围时return true
     * @param null $value
     * @param array|string $range
     * @return bool
     */
    static function between($value = '', $range = '')
    {
        if (is_string($range)) {
            $range = explode(',', $range);
        } elseif (!is_array($range)) {
            return false;
        }

        $max = max($range);
        $min = min($range);
        if ($value >= $min && $value <= $max) {
            return true;
        }

        return false;
    }


    /**
     * 当不存在指定范围时return true
     * @param null $value
     * @param array|string $range
     * @return bool
     */
    static function notBetween($value = '', $range = '')
    {
        if (is_string($range)) {
            $range = explode(',', $range);
        } elseif (!is_array($range)) {
            return false;
        }

        $max = max($range);
        $min = min($range);
        if ($value >= $min && $value <= $max) {
            return false;
        }

        return true;
    }

    /**
     * 当数据库中值存在时 return false
     * @param $val 值
     * @param $tableName 表名
     * @param $field 字段名
     * @return bool
     */
    static function unique($value, $tableName, $field)
    {
        $db = M();
        return !($db->where(array($field => $value))->count($tableName));
    }

    /**
     * 当字符长度存在指定范围时return true
     * @param null $value 字符串
     * @param array|string $range 范围
     * @return bool
     * length('abc',$rage = 3); strlen('abc') ==3
     * length('abc',$rage = array(5,3))==length('abc',$rage = array(3,5)) => strlen('abc') >=3 && strlen('abc') <=5
     */
    static function length($value = '', $range = '')
    {
        if (is_string($range)) {
            $range = explode(',', $range);
        } elseif (!is_array($range)) {
            return false;
        }

        $max = max($range);
        $min = min($range);
        $strLen = strlen($value);
        if ($max == $min) {
            if ($strLen == $max) {
                return true;
            }
        } elseif ($strLen >= $min && $strLen <= $max) {
            return true;
        }

        return false;
    }

    /**
     * Email格式验证
     * @param    string $value 需要验证的值
     */
    static function email($value)
    {
        $rules = "/\w+([-+.']\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/";

        if (!preg_match($rules, $value)) return false;

        return true;
    }

    /**
     * URL格式验证
     * @param    string $value 需要验证的值
     */
    static function url($value)
    {

        $rules = '/^(http|https)\:\/\/([\w-]+\.)+[\w-]+(\/[\w-.\/?%&=]*)?$/';
        if (!preg_match($rules, $value)) return false;

        return true;
    }

    /**
     * 数字格式验证
     * @param    string $value 需要验证的值
     */
    static function number($value)
    {
        return is_numeric($value);
    }


    /**
     * 使用自定义的正则表达式进行验证
     * @param    string $value 需要验证的值
     * @param    string $rules 正则表达式
     */
    static function regex($value, $rules)
    {
        if (!preg_match($rules, $value)) return false;

        return true;

    }


    /**
     * 判断是否为手机号码
     * @param    string $value 手机号码
     */
    static function phone($value = '')
    {

        $rules = '/^1\d{10}$/';
        if (!preg_match($rules, $value)) return false;

        return true;

    }


    /**
     * 判断验证码的确与否
     * @param string $value 值
     * @param string $code session中的key
     * @return bool
     */
    static function verifyCode($value = '', $code = 'verify')
    {

        if (!session_id()) return false;

        if (strtolower(session($code)) != strtolower($value)) return false;

        return true;

    }

    /**
     * @param $name
     * @param Closure $paramenters
     *
     * @example
    Validate::extend('test', function ($key, $val) {
     * if ($key > $val) return true;
     * return false;
     * });
     * var_dump(Validate::test(3, 2)); true
     */
    static function extend($ruleName, Closure $callback)
    {
        static::$rule[$ruleName] = $callback;
    }


    static function __callStatic($name, $paramenters)
    {
        if (isset(self::$rule[$name])) {
            return call_user_func_array(static::$rule[$name], $paramenters);
        }
    }
}