<?php
/**
 * Created by YrPHP.
 * User: Kwin
 * QQ: 284843370
 * Email: kwinwong@hotmail.com
 * GitHub: https://github.com/kwinH/YrPHP
 */
namespace YrPHP\Cache;


class Memcached implements ICache
{
    protected static $object;


    static function getInstance()
    {
        if (!extension_loaded('memcached')) {
            die('没有安装memcached扩展');
        }

        if (is_object(static::$object)) {
            return static::$object;
        } else {
            static::$object = new \Memcached;
            $config = C('memcache');
            if (is_string($config)) {
                $conf = explode(':', $config);
                static::$object->addserver($conf[0], $conf[1]);
            } elseif (is_array($config)) {
                foreach ($config as $v) {
                    $conf = explode(':', $v);
                    static::$object->addServer($conf[0], $conf[1]);
                }
            } else {
                die('参数错误');
            }

            return static::$object;
        }

    }


    /**
     * 如果不存在或则已过期则返回true
     * @param $key
     * @return bool
     */
    public function isExpired($key)
    {
        if ($this->get($key) !== false) {
            return false;
        }

        return true;
    }

    public function get($key = null)
    {
        if (is_null($key)) {
            return false;
        }

        return myUnSerialize(static::getInstance()->get($key));
    }


    public function set($key = '', $val = '', $timeout = null)
    {
        $timeout = is_null($timeout) ? C('dbCacheTime') : $timeout;
        return static::getInstance()->set($key, mySerialize($val), $timeout);
    }

    public function del($key = null)
    {
        if (is_null($key)) {
            return false;
        }

        return static::getInstance()->delete($key);
    }

    public function clear()
    {
        return static::getInstance()->flush();
    }
}