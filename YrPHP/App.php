<?php

/**
 * Created by YrPHP.
 * User: Nathan
 * QQ:284843370
 * Email:kwinwong@hotmail.com
 * GitHub:https://github.com/kwinH/YrPHP
 */

use YrPHP\Arr;
use YrPHP\Config;
use YrPHP\Debug;
use YrPHP\Session;
use YrPHP\Structure;

class App
{
    static $instanceList = array();

    static function init()
    {
        define('STARTTIME', microtime(true));
        //   ini_set('memory_limit', -1);
        set_time_limit(0);
        //PHP程序所有需要的路径，都使用绝对路径
        define("BASE_PATH", str_replace("\\", "/", dirname(__FILE__)) . '/'); //框架的路径
        define("ROOT_PATH", dirname(BASE_PATH) . '/'); //项目的根路径，也就是框架所在的目录
        define("APP_PATH", ROOT_PATH . rtrim(APP, '/') . '/'); //用户项目的应用绝对路径

        require ROOT_PATH . 'vendor/autoload.php';

        //注册自动加载函数
        spl_autoload_register('static::autoLoadClass');

        if (!file_exists(APP)) {
            Structure::run();
        }

    }


    /*
  /设置包含目录（类所在的全部目录）,  PATH_SEPARATOR 分隔符号 Linux(:) Windows(;)
  $include_path=get_include_path();                         //原基目录
  $include_path.=PATH_SEPARATOR.ROOT_PATH;       //框架中基类所在的目录
  //设置include包含文件所在的所有目录
  set_include_path($include_path);
  */
    static function autoLoadClass($className)
    {
        $file = BASE_PATH.'_class_alias.php';
        requireCache($file);

        if (!class_exists($className) && $name = Arr::arrayIGet(Config::get('classAlias'), $className)) {
            file_put_contents($file, PHP_EOL . 'class ' . ucfirst(strtolower($className)) . ' extends Facade{public static $className=\'' . $name . '\';}', FILE_APPEND);
            header('location: ' . $_SERVER['HTTP_REFERER']);
        }
    }

    static function loadConf()
    {
        //包含系统配置文件
        Config::load('Config');
        //包含自定义配置文件
        if (defined('APP_MODE')) {
            Config::load('config_' . APP_MODE);
        }

        date_default_timezone_set(Config::get('timezone')); //设置时区（默认中国）

        error_reporting(-1); //报告所有PHP错误
        if (Config::get('logRecord')) {
            ini_set('log_errors', 1); //设置是否将脚本运行的错误信息记录到服务器错误日志或者error_log之中
            $logFile = rtrim(Config::get('logDir'), '/') . '/sys_log_' . date("Y-m-d") . '.log';//定义日志文件名;
            ini_set('error_log', $logFile); //将错误信息写进日志 APP.'runtime/logs'/sys_log_' . date("Y-m-d") . '.log'
            //开启自定义错误日志
            set_error_handler(array('App', "yrError"));
        }

        if (!defined('DEBUG')) {
            define('DEBUG', false);
        }

        //错误信息是否显示
        if (DEBUG) {
            ini_set("display_errors", 1); //显示错误到屏幕
        } else {
            ini_set("display_errors", 0); //隐藏而不显示
        }

        if (isset($_GET['Lang'])) {
            Session::set('Lang', 'en');
        } else {
            if (!Session::get('Lang')) {
                Session::set('Lang', 'en');
            }
        }

        if (isset($_GET['country'])) {
            Session::set('country', strtoupper($_GET['country']));
        } else {
            if (!Session::get('country')) {
                Session::set('country', reset(explode(',', $_SERVER["HTTP_ACCEPT_LANGUAGE"])));
            }
        }
        $langPath = APP_PATH . 'Lang/lang_' . Session::get('Lang') . '.php';

        if (file_exists($langPath)) {
            getLang(require $langPath);
        }

    }


    /**
     * 错误处理函数
     * @param $errNo
     * @param $errStr
     * @param $errFile
     * @param $errLine
     * @return bool
     */
    static function yrError($errNo, $errStr, $errFile, $errLine)
    {
        $logFile = '%s_log_' . date("Y-m-d");//定义日志文件名;
        $template = '';

        switch ($errNo) {
            case E_USER_ERROR:
                $template .= "用户ERROR级错误，必须修复 错误编号[$errNo] $errStr ";
                $template .= "错误位置 文件$errFile,第 $errLine 行" . PHP_EOL;
                $logFile = sprintf($logFile, 'error');

                break;
            case E_WARNING://运行时警告（非致命的错误）2 
            case E_USER_WARNING:
                $template .= "用户WARNING级错误，建议修复 错误编号[$errNo] $errStr ";
                $template .= "错误位置 文件$errFile,第 $errLine 行" . PHP_EOL;
                $logFile = sprintf($logFile, 'warning');
                break;

            case E_NOTICE://运行时注意消息（可能是或者可能不是一个问题） 8
            case E_USER_NOTICE:
                $template .= "用户NOTICE级错误，不影响系统，可不修复 错误编号[$errNo] $errStr ";
                $template .= "错误位置 文件$errFile,第 $errLine 行" . PHP_EOL;
                $logFile = sprintf($logFile, 'notice');
                break;

            default:
                $template .= "未知错误类型: 错误编号[$errNo] $errStr  ";
                $template .= "错误位置 文件$errFile,第 $errLine 行" . PHP_EOL;
                $logFile = sprintf($logFile, 'unknown');
                break;
        }

        Debug::log($template, $logFile);
        return true;
    }


    static function run()
    {
        static::init();
        Config::load('class_alias', 'classAlias');
        Config::load('interface', 'interface');
        static::loadConf();
        header("Content-Type:" . Config::get('contentType') . ";charset=" . Config::get('charset')); //设置系统的输出字符为utf-8

        $url = uri::rsegment();

        $ctrBasePath = APP_PATH . Config::get('ctrBaseNamespace') . '/';

        //默认控制器文件
        $defaultCtl = Config::get('defaultCtl');

        //默认方法
        $defaultAct = Config::get('defaultAct');

        $classObj = APP . '\\' . Config::get('ctrBaseNamespace');

        if (Config::get('urlType') == 0) {
            //普通模式 GET
            if (empty($_GET[Config::get('ctlTrigger')])) {
                $className = $defaultCtl;
            } else {
                $url = explode('/', $_GET[Config::get('ctlTrigger')]);
                $className = ucfirst(end($url));
                array_pop($url);
                $classObj .= '\\' . implode('\\', $url);

            }

            $action = empty($_GET[Config::get('actTrigger')]) ? $defaultAct : strtolower($_GET[Config::get('actTrigger')]);

        } else {
            //(PATHINFO 模式)
            $module = '';
            foreach ($url as $k => $v) {
                $v = ucfirst(strtolower($v));
                if (is_dir($ctrBasePath . $v)) {
                    $module .= $v . '/';
                    $ctrBasePath .= empty($v) ? '' : $v . '/';
                    $classObj .= '\\' . $v;
                    unset($url[$k]);
                } else {
                    $className = ucfirst(strtolower($v));
                    $action = empty($url[$k + 1]) ? $defaultAct : strtolower($url[$k + 1]);
                    unset($url[$k], $url[$k + 1]);
                    break;
                }
            }

            if (!isset($className)) {
                $className = $defaultCtl;
                $action = $defaultAct;
            }

        }

        $url = array_values($url);

        $classObj .= '\\' . $className;
        $classPath = $ctrBasePath . $className . '.php';
        $nowAction = $classObj . '::' . $action;

        Config::set([
            'classPath' => $classPath,
            'module' => trim($module, '/'),
            'ctlName' => $className,
            'classObj' => $classObj,
            'actName' => $action,
            'nowAction' => $nowAction,
            'param' => $url,
            'Lang' => Session::get('Lang')
        ]);

        if (method_exists($classObj, $action)) {

            static::pipeline()
                ->send(static::request())
                ->through(Config::get('middleware.before'))
                ->then(function ($request) use ($classObj, $action, $url) {
                    $ctlObj = static::loadClass($classObj);
                    $middleware = array_merge(Config::get('middleware.middle'), $ctlObj->getMiddleware());

                    static::pipeline()
                        ->send($request)
                        ->through($middleware)
                        ->then(function ($request) use ($classObj, $action, $url) {
                            array_unshift($url, $classObj, $action);
                            $request->view = call_user_func_array('static::runMethod', $url);

                            static::pipeline()
                                ->send($request)
                                ->through(Config::get('middleware.after'))
                                ->then(function ($request) {
                                    echo $request->view;
                                    Session::delete(Session::get('flash', []));
                                    Session::set('flash', []);
                                });

                        });
                });

        } else {
            error404();
        }

        if (DEBUG && !request::isAjax()) {
            echo Debug::message();
        }
    }

    /**
     * @param array $argv
     */
    static function cli($argv)
    {
            if (count($argv) < 3) {
                exit('Parameter error');
            }

            static::init();
            Config::load('class_alias', 'classAlias');
            Config::load('interface', 'interface');
            Config::load('commands', 'commands');
            static::loadConf();

            $class = Config::get('commands.' . $argv[1]);
            if (is_null($class)) {
                $class = APP . '\\' . Config::get('ctrBaseNamespace') . '\\' . ucfirst(strtolower($argv[1]));
            }

            $method = $argv[2];

            if (class_exists($class)) {
                unset($argv[0], $argv[1], $argv[2]);
                $class = static::loadClass($class);
                call_user_func_array([$class, $method], $argv);
            }
    }

    /**
     * loadClass($className [, mixed $parameter [, mixed $... ]])
     * @param $className 需要得到单例对象的类名
     * @param $parameter $args 0个或者更多的参数，做为类实例化的参数。
     * @return  object
     */
    static function loadClass()
    {
        //取得所有参数
        $arguments = func_get_args();
        $key = md5(json_encode($arguments));
        //弹出第一个参数，这是类名，剩下的都是要传给实例化类的构造函数的参数了
        $className = array_shift($arguments);

        if (!isset(static::$instanceList[$key])) {
            $reflection = new ReflectionClass($className);
            if ($reflection->isInterface()) {
                $reflection = new ReflectionClass(Config::get('interface.' . $className));
            }

            $constructor = $reflection->getConstructor();

            if (is_null($constructor)) {
                static::$instanceList[$key] = $reflection->newInstanceArgs();
            } else {
                if (!$arguments) {
                    $arguments = static::getDependencies($constructor);
                }

                static::$instanceList[$key] = $reflection->newInstanceArgs($arguments);
            }

        }
        return static::$instanceList[$key];
    }


    /**
     * 递归解析参数
     * @param ReflectionMethod $rfMethod
     * @param array $params
     * @return array
     */
    static function getDependencies(ReflectionMethod $rfMethod, $params = [])
    {
        $instanceParams = [];

        if (!$rfMethod instanceof ReflectionMethod) {
            return $instanceParams;
        }

        foreach ($rfMethod->getParameters() as $param) {
            if ($dependency = $param->getClass()) {   //该参数不是对象
                $instanceParams[] = static::loadClass($dependency->name);
            } else {
                if ($argument = array_shift($params)) {
                    $instanceParams[] = $argument;
                } else if ($param->isDefaultValueAvailable()) {
                    $instanceParams[] = $param->getDefaultValue();
                } else {
                    $instanceParams[] = null;
                }
            }
        }
        return $instanceParams;
    }

    /**
     * 运行类方法 自动填充参数
     * @return mixed
     */
    public static function runMethod()
    {
        //取得所有参数
        $arguments = func_get_args();
        $className = array_shift($arguments);
        $MethodName = array_shift($arguments);
        $reflectionMethod = new ReflectionMethod($className, $MethodName);

        $args = static::getDependencies($reflectionMethod, $arguments);//返回类方法的参数

        return $reflectionMethod->invokeArgs(static::loadClass($className), $args);
    }

    public static function __callStatic($name, $paramenters)
    {
        $classAlias = Config::get('classAlias');
        if (isset($classAlias[$name]) || $name = Arr::arrayIGet($classAlias, $name)) {
            return loadClass($classAlias[$name], $paramenters);
        }
    }
}

if (substr(PHP_SAPI, 0, 3) == 'cli') {
    App::cli($argv);
} else {
    App::run();
}

