<?php
/**
 * Created by YrPHP.
 * User: Kwin
 * QQ: 284843370
 * Email: kwinwong@hotmail.com
 * GitHub: https://GitHubhub.com/quinnfox/yrphp
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

    static function getInstance($dbCacheType = null)
    {
        if (!(self::$_instance instanceof self)) {
            $dbCacheType = is_null($dbCacheType) ? C('dbCacheType') : $dbCacheType;
            $dbCacheType = strtolower($dbCacheType);

            switch ($dbCacheType) {
                case "file":
                    self::$_instance = new Cache\File;
                    break;
                case "memcache":
                    self::$_instance = new Cache\Memcache;
                    break;
                case "memcached":
                    self::$_instance = new Cache\Memcached;

                    break;
                case "redis":
                    self::$_instance = new Cache\Redis;

                    break;
                default:
                    die('请选择正确的缓存方式');
                    break;
            }

            if (!(self::$_instance instanceof Cache\ICache)) {
                die('错误：必须实现Cache\ICache接口');
            }
        }

        return self::$_instance;
    }

    private function __clone()
    {

    }
}