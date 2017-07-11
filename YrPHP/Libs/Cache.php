<?php
/**
 * Created by YrPHP.
 * User: Kwin
 * QQ: 284843370
 * Email: kwinwong@hotmail.com
 * GitHub: https://github.com/kwinH/YrPHP
 *
 * 缓存处理工厂
 */
namespace YrPHP;


class Cache
{
    static $_instance = null;

    private function __construct()
    {

    }

    /**
     * @param null $dbCacheType
     * @return null|Cache\File|Cache\Memcache|Cache\Memcached|Cache\Redis
     */
    public static function getInstance($dbCacheType = null)
    {
        if (!(static::$_instance instanceof self)) {
            $dbCacheType = is_null($dbCacheType) ? C('dbCacheType') : $dbCacheType;
            $dbCacheType = strtolower($dbCacheType);

            switch ($dbCacheType) {
                case "file":
                    static::$_instance = new Cache\File;
                    break;
                case "memcache":
                    static::$_instance = new Cache\Memcache;
                    break;
                case "memcached":
                    static::$_instance = new Cache\Memcached;

                    break;
                case "redis":
                    static::$_instance = new Cache\Redis;

                    break;
                default:
                    die('请选择正确的缓存方式');
                    break;
            }

            if (!(static::$_instance instanceof Cache\ICache)) {
                die('错误：必须实现Cache\ICache接口');
            }
        }

        return static::$_instance;
    }

    private function __clone()
    {

    }
}