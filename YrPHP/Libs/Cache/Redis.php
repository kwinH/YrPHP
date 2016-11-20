<?php
/**
 * Created by YrPHP.
 * User: Kwin
 * QQ: 284843370
 * Email: kwinwong@hotmail.com
 * GitHub: https://GitHubhub.com/quinnfox/yrphp
 */
namespace YrPHP\Cache;


class Redis implements ICache
{
    private static $object;

    static function getInstance()
    {
        if (!extension_loaded('redis')) {
            die('没有安装redis扩展');
        }
        if (is_object(self::$object)) {
            return self::$object;
        } else {
            self::$object = new \Redis;
            $config = C('redis');
            if (is_string($config)) $config = array($config);

            if (is_array($config)) {
                foreach ($config as $k => $v) {
                    $conf = explode(':', $v);
                    self::$object->connect($conf[0], $conf[1]);
                }
            } else {
                die('参数错误');
            }
            self::$object->select(0);
            return self::$object;
        }

    }

    /**
     * 如果不存在或则已过期则返回true
     */
    public function isExpired($key)
    {
        return !self::getInstance()->exists($key);
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
        return self::getInstance()->set($key, mySerialize($val), $timeout);
    }

    /**
     * 获得缓存
     * @param $key
     * @return mixed
     */
    public function get($key = null)
    {
        if (is_null($key)) return false;

        return myUnSerialize(self::getInstance()->get($key));
    }

    /**
     * 清空缓存
     * @return mixed
     */
    public function clear()
    {
        return self::getInstance()->Flushdb();
    }

    /**
     * 根据key值删除缓存
     * @param string $key
     */
    public function del($key = null)
    {
        if (is_null($key)) return false;

        $keys = self::getInstance()->keys("*{$key}*");
        return self::getInstance()->delete($keys);
    }

}