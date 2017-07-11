<?php
/**
 * Created by PhpStorm.
 * User: TOYOTA
 * Date: 2016/12/12
 * Time: 15:49
 */

namespace YrPHP;

class Request
{
    protected $exceptKey = [];
    protected $onlyKey = [];
    protected static $getData = [];
    protected static $postData = [];
    //最后生成的视图
    public $view = '';

    public function __construct()
    {
        static::$getData = array_map([$this, 'filter'], $_GET);//回调过滤数据;
        static::$postData = array_map([$this, 'filter'], $_POST);
    }


    /**
     * 支持连贯查询
     * @param $keys
     * @return $this
     */
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


    public function header($key = null, $default = null)
    {
        $headers = [];
        if (!function_exists('getallheaders')) {
            foreach ($_SERVER as $name => $value) {
                if (substr($name, 0, 5) == 'HTTP_') {
                    $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
                }
            }

        } else {
            $headers = getallheaders();
        }

        if (is_null($key)) {
            return $headers;
        }

        if ($value = Arr::arrayIGet($headers, $key)) {
            return $value;
        }
        return $default;

    }


    public function get($key = null, $default = null)
    {
        $data = static::$getData;

        if (is_null($key)) {
            return $this->all($data);
        } else {
            return isset($data[$key]) ? $data[$key] : $default;
        }

    }

    public function post($key = null, $default = null)
    {
        $data = static::$postData;

        if (is_null($key)) {
            return $this->all($data);
        } else {
            return isset($data[$key]) ? $data[$key] : $default;
        }

    }

    public function all($data = null)
    {
        $data = $data ?: array_merge(static::$getData, static::$postData);
        if ($this->onlyKey) {
            Arr::only($data, $this->onlyKey);

        } else if ($this->exceptKey) {
            Arr::except($data, $this->exceptKey);
        }

        $this->onlyKey = $this->exceptKey = [];
        return $data;
    }

    public function replace($data, $method = 'get')
    {
        switch ($method) {
            case 'post':
                static::$postData = $data;
                break;
            case 'get':
                static::$getData = $data;
                break;
            default:
                return false;
        }

        return $data;
    }

    public function merge(array $data, $method = 'get')
    {
        switch ($method) {
            case 'post':
                static::$postData = array_merge(static::$postData, $data);
                break;
            case 'get':
                static::$getData = array_merge(static::$getData, $data);
                break;
            default:
                return false;
        }
    }

    public function pop(array $keys, $method = 'get')
    {

        $keys = array_flip($keys);

        switch ($method) {
            case 'post':
                $data = static::$postData;
                break;
            case 'get':
                $data = static::$getData;
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
                if (is_array($data)) {
                    array_map($filter, $data);
                } else {
                    $data = $filter($data);
                }
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
        return \uri::segment($n, $no_result);
    }

    /**
     * 返回路由替换过后的uri 数组
     * @param null $n
     * @param null $no_result
     * @return array|mixed|null|string
     */
    function rpart($n = null, $no_result = null)
    {
        return \uri::rsegment($n, $no_result);
    }

    /**
     *返回没有经过路由替换过的uri字符串
     * @return string
     */
    public function getPath()
    {
        return \uri::getPath();
    }

    public function is($rule)
    {
        $rule = preg_quote($rule, '/');
        $rule = str_replace('\*', '.*', $rule) . '\z';

        $path = $this->getPath();
        return (bool)preg_match('/' . $rule . '/Ui', $path);


    }

    /**
     * 返回经过路由替换过的uri字符串
     * @return string
     */
    public function getRPath()
    {
        return \uri::parseRoutes();
    }

    /**
     * 在问号 ? 之后的所有字符串
     * @return mixed
     */
    public function getQuery()
    {
        return \uri::getQuery();
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


    public function method()
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    /**
     * 判断是不是 POST 请求
     * @return    bool
     */
    public function isPost()
    {
        return ($_SERVER['REQUEST_METHOD'] == 'POST');
    }

    /**
     * 判断是否SSL协议
     * @return boolean
     */
    public function isHttps()
    {
        if (
            (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off')
            || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
            || (!empty($_SERVER['HTTP_FRONT_END_HTTPS']) && strtolower($_SERVER['HTTP_FRONT_END_HTTPS']) !== 'off')
        ) {
            return true;
        }

        return false;
    }

    public function port()
    {
        return $_SERVER['SERVER_PORT'];
    }

    public function host()
    {
        return $_SERVER['HTTP_HOST'];
    }

    function currentUrl()
    {
        return ($this->isHttps() ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER["REQUEST_URI"];
    }

    public function referer()
    {
        return isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : $_SERVER['HTTP_HOST'];
    }

}