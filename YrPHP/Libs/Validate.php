<?php
/**
 * Created by YrPHP.
 * User: Kwin
 * QQ: 284843370
 * Email: kwinwong@hotmail.com
 * GitHub: https://GitHubhub.com/kwinH/YrPHP
 */
namespace YrPHP;

class Validate
{


    /**
     * 当两个值相等时 return true
     * @param string $data
     * @param string $val
     * @return bool
     */
    static function equal($data = null, $val = null)
    {
        if (is_null($data)) return false;

        if ($data == $val) return true;

        return false;
    }

    /**
     * 当两个不值相等时 return true
     * @param string $data
     * @param string $val
     * @return bool
     */
    static function notEqual($data = null, $val = null)
    {
        if (is_null($data)) return false;

        if ($data != $val) return true;

        return false;
    }

    /**
     * 当存在指定范围时return true
     * @param string $data
     * @param array|string $range
     * @return bool
     */
    static function in($data = '', $range = '')
    {
        if (is_string($range)) {
            $range = explode(',', $range);
        } elseif (!is_array($range)) {
            return false;
        }

        if (in_array($range, $data)) return true;

        return false;
    }


    /**
     * 当不存在指定范围时return true
     * @param null $data
     * @param array|string $range
     * @return bool
     */
    static function notIn($data = '', $range = '')
    {
        if (is_string($range)) {
            $range = explode(',', $range);
        } elseif (!is_array($range)) {
            return false;
        }

        if (in_array($range, $data)) return false;

        return true;
    }


    /**
     * 当存在指定范围时return true
     * @param null $data
     * @param array|string $range
     * @return bool
     */
    static function between($data = '', $range = '')
    {
        if (is_string($range)) {
            $range = explode(',', $range);
        } elseif (!is_array($range)) {
            return false;
        }

        $max = max($range);
        $min = min($range);
        if ($data >= $min && $data <= $max) {
            return true;
        }

        return false;
    }


    /**
     * 当不存在指定范围时return true
     * @param null $data
     * @param array|string $range
     * @return bool
     */
    static function notBetween($data = '', $range = '')
    {
        if (is_string($range)) {
            $range = explode(',', $range);
        } elseif (!is_array($range)) {
            return false;
        }

        $max = max($range);
        $min = min($range);
        if ($data >= $min && $data <= $max) {
            return false;
        }

        return true;
    }

    /**
     * 当数据库中值存在时 return false
     * @param $tableName 表名
     * @param $field 字段名
     * @param $val 值
     * @return bool
     */
    static function unique($tableName, $field, $val)
    {
        $db = Model();
        return $count = $db->where(array($field => $val))->count($tableName);
    }

    /**
     * 当字符长度存在指定范围时return true
     * @param null $data 字符串
     * @param array|string $range 范围
     * @return bool
     * length('abc',$rage = 3); strlen('abc') ==3
     * length('abc',$rage = array(5,3))==length('abc',$rage = array(3,5)) => strlen('abc') >=3 && strlen('abc') <=5
     */
    static function length($data = '', $range = '')
    {
        if (is_string($range)) {
            $range = explode(',', $range);
        } elseif (!is_array($range)) {
            return false;
        }

        $max = max($range);
        $min = min($range);
        $strLen = strlen($data);
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
}