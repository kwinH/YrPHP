<?php
/**
 * Created by YrPHP.
 * User: Kwin
 * QQ: 284843370
 * Email: kwinwong@hotmail.com
 * GitHub: https://GitHubhub.com/quinnfox/yrphp
 */
namespace Yrphp\Cache;


interface   ICache
{
    /**
     * 如果不存在或则已过期则返回true
     */
    public function isExpired($key);


    /**
     * 设置缓存
     * @param string $key 要设置值的key
     * @param string $val 要存储的数据
     * @param null $timeout 有效期单位秒 0代表永久 默认为配置文件中的cacheLifetime
     * @return bool
     */
    public function set($key, $val, $timeout = null);

    /**
     * 获取缓存
     * @param $key
     * @return mixed
     */
    public function get($key = null);

    /**
     * 清空缓存
     * @return mixed
     */
    public function clear();

    /**
     * 根据key值删除缓存
     * @param string $key
     */
    public function del($key = null);


}