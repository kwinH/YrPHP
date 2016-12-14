<?php
/**
 * Created by YrPHP.
 * User: Kwin
 * QQ:284843370
 * Email:kwinwong@hotmail.com
 * GitHub:https://github.com/kwinH/YrPHP
 */

namespace YrPHP;


class Uri
{
    public $url;
    public $path;
    public $query;
    protected $routes = array();

    public function __construct()
    {

        if (file_exists(APP_PATH . 'Config/route.php')) {
            require_once APP_PATH . 'Config/route.php';
        } else {
            require_once BASE_PATH . 'Config/route.php';
        }
        if (isset($route)) {
            $this->routes = $route;
        }

        $this->parseUrl();
    }

    /**
     * 解析URL
     * @return array|string
     */
    public function parseUrl()
    {
        if (!isset($_SERVER['REQUEST_URI'], $_SERVER['SCRIPT_NAME'])) {
            return '';
        }

        $uri = parse_url($_SERVER['REQUEST_URI']);

        $this->query = isset($uri['query']) ? $uri['query'] : '';
        $path = isset($uri['path']) ? $uri['path'] : '';
        $this->path = str_replace(C('urlSuffix'), '', $path);


        //判断URL是否包含入口文件index.php及路径 如果有则去除，没有则只去除路径
        //$_SERVER['SCRIPT_NAME'] 包含当前脚本的路径
        if (strpos($path, $_SERVER['SCRIPT_NAME']) === 0) {
            //$this->path = (string)substr($this->path, strlen($_SERVER['SCRIPT_NAME']));
            $this->path = (string)str_replace($_SERVER['SCRIPT_NAME'], '', $this->path);

        } elseif (strpos($path, dirname($_SERVER['SCRIPT_NAME'])) === 0) {
            $this->path = (string)substr($this->path, strlen(dirname($_SERVER['SCRIPT_NAME'])));
        }

        $this->path = trim($this->path, '/');

        return array('path' => $this->path, 'query' => $this->query);

    }


    /**
     * 返回路由替换过后的uri 数组
     * @param null $n
     * @param null $no_result
     * @return array|mixed|null|string
     */
    public function rsegment($n = null, $no_result = null)
    {
        $uri = $this->parseRoutes();

        $uri = empty($uri) ? [] : explode('/', $uri);

        if (is_int($n)) return isset($uri[$n]) ? $uri[$n] : $no_result;

        return $uri;
    }

    public function rpart($n = null, $no_result = null)
    {
        return $this->rsegment($n = null, $no_result = null);
    }


    /**
     * 路由验证
     * @return mixed|string
     */
    public function parseRoutes()
    {
        $uri = $this->path;

        foreach ($this->routes as $k => $v) {
            $k = str_replace('/', '\/', $k);
            if (preg_match("/$k/", $uri)) {
                $v = str_replace(':', "\\", $v);
                $uri = preg_replace("/$k/", $v, $uri);
            }
        }

        return $uri;
    }

    /**返回没有经过路由替换过的uri数组
     * @param null $n
     * @param null $no_result
     * @return array|mixed|null|string
     */
    public function segment($n = null, $no_result = null)
    {
        $uri = empty($this->path) ? [] : explode('/', $this->path);

        if (is_int($n)) return isset($uri[$n]) ? $uri[$n] : $no_result;

        return $uri;
    }


    public function part($n = null, $no_result = null)
    {
        return $this->segment($n = null, $no_result = null);
    }

    /**
     *返回没有经过路由替换过的uri字符串
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * 返回经过路由替换过的uri字符串
     * @return string
     */
    public function getRPath()
    {
        return $this->parseRoutes();
    }

    /**
     * 在问号 ? 之后的所有字符串
     * @return mixed
     */
    public function getQuery()
    {
        return $this->query;
    }
}