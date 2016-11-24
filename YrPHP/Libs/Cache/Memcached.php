<?php
/**
 * Created by YrPHP.
 * User: Kwin
 * QQ: 284843370
 * Email: kwinwong@hotmail.com
 * GitHub: https://GitHubhub.com/quinnfox/yrphp
 */
namespace YrPHP\Cache;


class Memcached implements ICache
{
    private static $object;


    static function getInstance()
    {
        if (!extension_loaded('memcached')) {
            die('没有安装memcached扩展');
        }

        if (is_object(self::$object)) {
            return self::$object;
        } else {
            self::$object = new \Memcached;
            $config = C('memcache');
            if (is_string($config)) $config = array($config);

            if (is_array($config)) {
                foreach ($config as $k => $v) {
                    $conf = explode(':', $v);
                    self::$object->addserver($conf[0], $conf[1]);
                }
            } else {
                die('参数错误');
            }

            return self::$object;
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
        if (is_null($key)) return false;

        return myUnSerialize(self::getInstance()->get($key));
    }


    public function set($key = '', $val = '', $timeout = null)
    {
        $timeout = is_null($timeout) ? C('dbCacheTime') : $timeout;
        return self::getInstance()->set($key, mySerialize($val), $timeout);
    }

    public function del($key = null)
    {
        if (is_null($key)) return false;

        return self::getInstance()->delete($key);
    }

    public function clear()
    {
        return self::getInstance()->flush();
    }
}