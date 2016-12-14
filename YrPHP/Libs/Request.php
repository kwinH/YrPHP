<?php
/**
 * Created by PhpStorm.
 * User: TOYOTA
 * Date: 2016/12/12
 * Time: 15:49
 */

namespace YrPHP;

use App;

class Request
{
    protected $exceptKey = [];
    protected $onlyKey = [];
    protected static $getData = [];
    protected static $postData = [];

    public function __construct()
    {
        self::$getData = array_map([$this, 'filter'], $_GET);//回调过滤数据;
        self::$postData = array_map([$this, 'filter'], $_POST);
    }


    public function except($keys)
    {
        $keys = is_array($keys) ? $keys : func_get_args();

        $this->exceptKey = $keys;
        return $this;
    }

    public function only($keys)
    {
        $keys = is_array($keys) ? $keys : func_get_args();

        $this->onlyKey = $keys;
        return $this;
    }

    public function get($key = null, $defualt = null)
    {
        $data = self::$getData;

        if (is_null($key)) {
            return $this->all($data);
        } else {
            return isset($data[$key]) ? $data[$key] : $defualt;
        }

    }

    public function post($key = null, $defualt = null)
    {
        $data = self::$postData;

        if (is_null($key)) {
            return $this->all($data);
        } else {
            return isset($data[$key]) ? $data[$key] : $defualt;
        }

    }

    public function all($data = null)
    {
        $data = $data ?: array_merge(self::$getData, self::$postData);
        if ($this->onlyKey) {
            $this->onlyKey = array_flip($this->onlyKey);
            $data = array_intersect_key($data, $this->onlyKey);

        } else if ($this->exceptKey) {
            $this->exceptKey = array_flip($this->exceptKey);
            $data = array_diff_key($data, $this->exceptKey);
        }

        $this->onlyKey = $this->exceptKey = [];
        return $data;
    }

    public function replace($data, $method = 'get')
    {
        switch ($method) {
            case 'post':
                self::$postData = $data;
                break;
            case 'get':
                self::$getData = $data;
                break;
        }

        return $data;
    }

    public function merge(array $data, $method = 'get')
    {
        switch ($method) {
            case 'post':
                self::$postData = array_merge(self::$postData, $data);
                break;
            case 'get':
                self::$getData = array_merge(self::$getData, $data);
                break;
        }
    }

    public function pop(array $keys, $method = 'get')
    {

        $keys = array_flip($keys);

        switch ($method) {
            case 'post':
                $data = self::$postData;
                break;
            case 'get':
                $data = self::$getData;
                break;
            default:
                return false;
        }

        $data = array_diff_key($data, $keys);
        $this->replace($data, $method);
        return $data;
    }

    public function filter($data = [])
    {
        $filters = C('defaultFilter');
        if (is_string($filters)) {
            $filters = explode('|', $filters);
        }
        foreach ($filters as $filter) {
            if (function_exists($filter)) {
                $data = $filter($data);
            }
        }

        return $data;
    }

    /**返回没有经过路由替换过的uri数组
     * @param null $n
     * @param null $no_result
     * @return array|mixed|null|string
     */
    function part($n = null, $no_result = null)
    {
        return App::uri()->segment($n = null, $no_result = null);
    }

    /**
     * 返回路由替换过后的uri 数组
     * @param null $n
     * @param null $no_result
     * @return array|mixed|null|string
     */
    function rpart($n = null, $no_result = null)
    {
        return App::uri()->rsegment($n = null, $no_result = null);
    }

    /**
     *返回没有经过路由替换过的uri字符串
     * @return string
     */
    public function getPath()
    {
        return App::uri()->path;
    }

    public function is($rule)
    {
        $rule = addslashes(trim($rule, '/'));
        var_dump($rule);
        $path = $this->getPath();
        return preg_match('/' . $rule . '/Ui', $path);
    }

    /**
     * 返回经过路由替换过的uri字符串
     * @return string
     */
    public function getRPath()
    {
        return App::uri()->parseRoutes();
    }

    /**
     * 在问号 ? 之后的所有字符串
     * @return mixed
     */
    public function getQuery()
    {
        return App::uri()->query;
    }

    /**
     * 判断是不是 AJAX 请求
     * 测试请求是否包含HTTP_X_REQUESTED_WITH请求头。
     * @return    bool
     */
    public function isAjax()
    {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
    }


    /**
     * 判断是不是 POST 请求
     * @return    bool
     */
    public function isPost()
    {
        return ($_SERVER['REQUEST_METHOD'] == 'POST');
    }

    public function prot()
    {
        return $_SERVER['SERVER_PORT'];
    }

    public function referer()
    {
        return $_SERVER['HTTP_REFERER'];
    }

    /**
     * 判断是否SSL协议
     * @return boolean
     */
    public function isHttps()
    {
        if (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') {
            return true;
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            return true;
        } elseif (!empty($_SERVER['HTTP_FRONT_END_HTTPS']) && strtolower($_SERVER['HTTP_FRONT_END_HTTPS']) !== 'off') {
            return true;
        }

        return false;
    }

}