<?php
/**
 * Created by YrPHP.
 * User: Kwin
 * QQ: 284843370
 * Email: kwinwong@hotmail.com
 * GitHub: https://github.com/kwinH/YrPHP
 */
namespace YrPHP\Cache;


class Redis implements ICache
{
    protected static $object;

    static function getInstance()
    {
        if (!extension_loaded('redis')) {
            die('没有安装redis扩展');
        }
        if (is_object(static::$object)) {
            return static::$object;
        } else {
            static::$object = new \Redis;
            $config = C('redis');
            if (is_string($config)) {
                $conf = explode(':', $config);
                static::$object->connect($conf[0], $conf[1]);
            } elseif (is_array($config)) {
                foreach ($config as $v) {
                    $conf = explode(':', $v);
                    static::$object->connect($conf[0], $conf[1]);
                }
            } else {
                die('参数错误');
            }
            static::$object->select(0);
            return static::$object;
        }

    }

    /**
     * 如果不存在或则已过期则返回true
     */
    public function isExpired($key)
    {
        return !static::getInstance()->exists($key);
    }


    /**
     * 设置缓存
     * @param $key
     * @param $val
     * @return mixed
     */
    public function set($key = '', $val = '', $timeout = null)
    {
        $timeout = is_null($timeout) ? C('dbCacheTime') : $timeout;
        return static::getInstance()->set($key, mySerialize($val), $timeout);
    }

    /**
     * 获得缓存
     * @param $key
     * @return mixed
     */
    public function get($key = null)
    {
        if (is_null($key)) {
            return false;
        }

        return myUnSerialize(static::getInstance()->get($key));
    }

    /**
     * 清空缓存
     * @return mixed
     */
    public function clear()
    {
        return static::getInstance()->Flushdb();
    }

    /**
     * 根据key值删除缓存
     * @param string $key
     */
    public function del($key = null)
    {
        if (is_null($key)) {
            return false;
        }

        $keys = static::getInstance()->keys("*{$key}*");
        return static::getInstance()->delete($keys);
    }

}