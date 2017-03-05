<?php
/**
 * Created by YrPHP.
 * User: Kwin
 * QQ:284843370
 * Email:kwinwong@hotmail.com
 * GitHub:https://github.com/kwinH/YrPHP
 */
namespace YrPHP;


use App;
use Request;

abstract class Controller
{
    private static $instance;

    /**
     *
     * 在控制器上注册的中间件
     * @var array
     */
    protected $middleware = [];

    function __construct()
    {
        self::$instance =& $this;

    }


    /**
     *
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
     * 注册中间件
     *
     * @param  string $middleware
     * @param  array $options
     * @return void
     */
    public function middleware($middleware, array $options = [])
    {
        $this->middleware[APP . '\Middleware\\' . $middleware] = $options;
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
     * 验证POST|GET提交的数据
     * @param array $rule 验证规则
     */
    public function validate($rule = [])
    {
        App::loadClass(FormRequest::class, App::loadClass('\YrPHP\Request'), $rule);
    }

    public function errorBackTo($errors)
    {
        session('errors', $errors);
        if (Request::isAjax()) {
            exit(Request::toJson(['error' => $errors]));
        }

        if (Request::isPost()) {
            gotoUrl(Request::referer());
        }
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