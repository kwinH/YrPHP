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
                self::set($k, $v);
            }
        } else {
            $array = &self::$config;
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
                self::delete($k);
            }
        } else {
            $array = &self::$config;
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
     * @param null $defualt
     * @return null
     */
    public static function get($key = '', $defualt = null)
    {
        if (empty($key)) return self::$config;

        $config = self::$config;
        foreach (explode('.', $key) as $v) {
            if (!isset($config[$v])) return $defualt;
            $config = $config[$v];
        }
        return $config;
    }


    public static function all()
    {
        return self::$config;
    }

    public static function load($fileName = '', $key = null)
    {
        $config = requireCache(APP_PATH . 'Config/' . $fileName . '.php');

        if (!$config) return false;

        if (is_null($key)) {
            self::set($config);
        } else {
            self::set($key, $config);
        }

        return self::$config;
    }


}