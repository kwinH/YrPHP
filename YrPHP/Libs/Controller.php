<?php
/**
 * Created by YrPHP.
 * User: Kwin
 * QQ:284843370
 * Email:kwinwong@hotmail.com
 * GitHub:https://github.com/kwinH/YrPHP
 */
namespace YrPHP;


abstract class Controller
{
    private static $instance;

    /**
     * 在控制器上注册的中间件
     *
     * @var array
     */
    protected $middleware = [];

    function __construct()
    {
        self::$instance =& $this;

    }


    /**
     * 定义一个静态全局Controller超级对象
     * 可通过引用的方式使用Controller对象
     * 返回当前实例控制器对象
     * @static
     * @return    object
     */
    public static function &getInstance()
    {
        return self::$instance;
    }


    /**
     * Register middleware on the controller.
     *
     * @param  string $middleware
     * @param  array $options
     * @return void
     */
    public function middleware($middleware, array $options = [])
    {
        $this->middleware[$middleware] = $options;
    }

    public function getMiddleware()
    {
        $middleware = [];
        foreach ($this->middleware as $k => $v) {
            if (empty($v)) {
                $middleware[] = $k;
                continue;
            }
            $actName = C('actName');

            if (isset($v['only']) && in_array($actName, $v['only'])) {
                $middleware[] = $k;
            } elseif (isset($v['except']) && !in_array($actName, $v['except'])) {
                $middleware[] = $k;
            }
        }
        return $middleware;
    }

    /**
     * 重写这个方法 在构造函数中调用
     * 缓存初始化 判断缓存ID是否合理 避免生成无用静态文件
     */
    private function checkCacheId()
    {

    }

    public function __call($method, $args)
    {
        error404();
    }

    public function __get($name)
    {
        if (class_exists($name)) {
            return loadClass($name);
        }
    }


}