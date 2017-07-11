<?php
/**
 * Created by PhpStorm.
 * User: TOYOTA
 * Date: 2016/12/16
 * Time: 10:59
 */

namespace YrPHP;


class Config
{
    public static $config = [];

    private function __construct()
    {

    }

    /**
     * @param string|array $key
     * @param string $value
     */
    public static function set($key = '', $value = '')
    {
        if ($value === '' && is_array($key)) {
            foreach ($key as $k => $v) {
                static::set($k, $v);
            }
        } else {
            $array = &static::$config;
            $keys = explode('.', $key);
            while (count($keys) > 1) {
                $key = array_shift($keys);

                if (!isset($array[$key]) || !is_array($array[$key])) {
                    $array[$key] = [];
                }

                $array = &$array[$key];
            }
            $array[array_shift($keys)] = $value;
        }

    }

    /**
     * @param string|array $key
     */
    public static function delete($key)
    {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                static::delete($k);
            }
        } else {
            $array = &static::$config;
            $keys = explode('.', $key);
            while (count($keys) > 1) {
                $key = array_shift($keys);
                $array = &$array[$key];
            }
            unset($array[array_shift($keys)]);
        }
    }

    /**
     * @param string $key
     * @param null $default
     * @return null
     */
    public static function get($key = '', $default = null)
    {
        if (empty($key)) {
            return static::$config;
        }

        $config = static::$config;
        foreach (explode('.', $key) as $v) {
            if (!isset($config[$v])) {
                return $default;
            }
            $config = $config[$v];
        }
        return $config;
    }


    public static function all()
    {
        return static::$config;
    }

    public static function load($fileName = '', $key = null)
    {
        $config = requireCache(APP_PATH . 'Config/' . $fileName . '.php');

        if (!$config) {
            return false;
        }

        if (is_null($key)) {
            static::set($config);
        } else {
            static::set($key, $config);
        }

        return static::$config;
    }


}