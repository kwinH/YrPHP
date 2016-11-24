<?php
/**
 * Created by YrPHP.
 * User: Kwin
 * QQ: 284843370
 * Email: kwinwong@hotmail.com
 * GitHub: https://GitHubhub.com/kwinH/YrPHP
 */
namespace YrPHP\Cache;


class Memcache implements ICache
{
    private static $object;

    static function getInstance()
    {
        if (!extension_loaded('memcache')) {
            die('没有安装memcache扩展');
        }

        if (is_object(self::$object)) {
            return self::$object;
        } else {
            self::$object = new \Memcache;
            $config = C('memcache');
            if (is_string($config)) {
                $conf = explode(':', $config);

                self::$object->connect($conf[0], $conf[1]);
            } elseif (is_array($config)) {
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


    /**
     * @param string $key
     * @param string $val
     * @param null $timeout
     * @return bool
     */
    public function set($key, $val, $timeout = null)
    {
        $timeout = is_null($timeout) ? C('dbCacheTime') : $timeout;
        return self::getInstance()->set($key, mySerialize($val), 0, $timeout);
    }

    public function del($key = null)
    {
        if(is_null($key)) return false;

        return self::getInstance()->delete($key);
    }

    public function clear()
    {
        return self::getInstance()->flush();
    }
}