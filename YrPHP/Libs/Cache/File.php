<?php
/**
 * Created by YrPHP.
 * User: Kwin
 * QQ: 284843370
 * Email: kwinwong@hotmail.com
 * GitHub: https://GitHubhub.com/quinnfox/yrphp
 */
namespace YrPHP\Cache;


class File implements ICache
{

    private $dbCacheTime;
    private $dbCachePath;
    private $dbCacheExt;

    public function __construct()
    {
        $this->dbCacheTime = C('dbCacheTime');
        $this->dbCachePath = C('dbCachePath');
        $this->dbCacheExt = C('dbCacheExt');
    }

    /**
     * 如果不存在或则已过期则返回true
     * @param $key
     * @return bool
     */
    public function isExpired($key)
    {
        $file = $this->dbCachePath . $key . '.' . $this->dbCacheExt;
        if (!file_exists($file)) return true;

        $contents = myUnSerialize(file_get_contents($this->dbCachePath . $key . '.' . $this->dbCacheExt));

        if ($contents['ttl'] != 0 && $contents['ttl'] > $contents['time']) {
            \Yrphp\File::rm($file);
            return true;
        }

        return false;
    }

    public function set($key, $val, $timeout = null)
    {

        $contents = array(
            'time' => time(),
            'ttl' => is_null($timeout) ? $this->dbCacheTime : $timeout,
            'data' => $val
        );


        return file_put_contents($this->dbCachePath . $key . '.' . $this->dbCacheExt, mySerialize($contents));
    }

    public function get($key = null)
    {
        if (is_null($key)) return false;

        $file = $this->dbCachePath . $key . '.' . $this->dbCacheExt;
        if (!file_exists($file)) return false;

        $contents = myUnSerialize(file_get_contents($this->dbCachePath . $key . '.' . $this->dbCacheExt));

        if ($contents['ttl'] != 0 && $contents['ttl'] > $contents['time']) {
            \Yrphp\File::rm($file);
            return false;
        }

        return $contents['data'];
    }

    public function clear()
    {
        \Yrphp\File::rm();
    }

    /**
     *
     * @param string $key
     */
    public function del($key = null)
    {
        if (is_null($key)) return false;

        $file = \Yrphp\File::search($this->dbCachePath, $key);

        foreach ($file as $k => $v) {
            \Yrphp\File::rm($v);
        }
    }


}