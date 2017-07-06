<?php

namespace YrPHP;

class Session
{
    protected static $init = null;

    /**
     * 构造函数
     */
    private function __construct()
    {

    }


    public static function init()
    {
        if (is_null(static::$init)) {
            $config = Config::get('session');

            session_name($config['name']);

            //    ini_set('session.save_handler', $config['saveHandler']);

            if ($config['saveHandler'] == 'files' && !$config['savePath']) {
                File::mkDir($config['savePath']);
            }
            session_save_path($config['savePath']);

            ini_set('session.gc_maxlifetime', $config['expire']);
            ini_set('session.cookie_lifetime', $config['expire']);

            ini_set('session.cookieDomain', $config['domain']);

            $className = '\YrPHP\Session\\' . ucwords(strtolower($config['saveHandler']));
            if (class_exists($className)) {
                session_set_save_handler(new $className);
            }

            session_start(); // 这也是必须的，打开session，必须在session_set_save_handler后面执行
            static::$init = true;
        }
    }

    /**
     * @param string|array $key
     * @param string $value
     */
    public static function set($key = '', $value = '')
    {
        static::init();
        if ($value === '' && is_array($key)) {
            foreach ($key as $k => $v) {
                static::set($k, $v);
            }
        } else {
            $array = &$_SESSION;
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
     * Flash a key / value pair to the session.
     *
     * @param  string $key
     * @param  mixed $value
     * @return void
     */
    public static function flash($key, $value)
    {
        static::init();
        static::set($key, $value);
        $arr = static::get('flash', []);
        array_push($arr, $key);
        static::set('flash', $arr);

    }


    /**
     * @param string $key
     * @param null $default
     * @return null
     */
    public static function get($key = '', $default = null)
    {
        static::init();
        if (empty($key)) {
            return $_SESSION;
        }

        $session = $_SESSION;
        foreach (explode('.', $key) as $v) {
            if (!isset($session[$v])) {
                return $default;
            }
            $session = $session[$v];
        }
        return $session;
    }


    public static function all()
    {
        static::init();
        return $_SESSION;
    }


    /**
     * @param string|array $key
     */
    public static function delete($key)
    {
        static::init();
        if (is_array($key)) {
            foreach ($key as $v) {
                static::delete($v);
            }
        } else {
            $array = &$_SESSION;
            $keys = explode('.', $key);
            while (count($keys) > 1) {
                $key = array_shift($keys);
                $array = &$array[$key];
            }
            unset($array[array_shift($keys)]);
        }
    }

    public static function clear()
    {
        static::init();
        $_SESSION = [];
    }

    /**
     * 销毁session
     * @return void
     */
    public static function destroy()
    {
        static::init();
        session_unset();
        session_destroy();
    }
}