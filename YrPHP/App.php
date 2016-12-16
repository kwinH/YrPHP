<?php

/**
 * Created by YrPHP.
 * User: Nathan
 * QQ:284843370
 * Email:kwinwong@hotmail.com
 * GitHub:https://github.com/kwinH/YrPHP
 */

use YrPHP\Config;
use YrPHP\Debug;
use YrPHP\File;
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

        if (!file_exists(APP)) Structure::run();

    }


    static function loadConf()
    {
        //包含系统配置文件
        Config::load('Config');
        //包含自定义配置文件
        if (defined('APP_MODE')) {
            Config::load('config_' . APP_MODE);
        }

        Config::load('class_alias', 'classAlias');
        Config::load('interface', 'interface');

        header("Content-Type:" . Config::get('contentType') . ";charset=" . Config::get('charset')); //设置系统的输出字符为utf-8
        date_default_timezone_set(Config::get('timezone')); //设置时区（默认中国）

        if ($sessionName = Config::get('sessionName'))
            session_name($sessionName);


        if ($sessionPath = Config::get('sessionSavePath')) {
            if (!file_exists($sessionPath)) File::mkDir($sessionPath);
            session_save_path($sessionPath);
        }

        if ($sessionExpire = Config::get('sessionExpire')) {
            ini_set('session.gc_maxlifetime', $sessionExpire);
            ini_set('session.cookie_lifetime', $sessionExpire);
        }

        ini_set('session.cookieDomain', Config::get('sessionDomain'));


        error_reporting(-1); //报告所有PHP错误
        if (Config::get('logRecord')) {
            ini_set('log_errors', 1); //设置是否将脚本运行的错误信息记录到服务器错误日志或者error_log之中
            $logFile = rtrim(Config::get('logDir'), '/') . '/sys_log_' . date("Y-m-d") . '.log';//定义日志文件名;
            ini_set('error_log', $logFile); //将错误信息写进日志 APP.'runtime/logs'/sys_log_' . date("Y-m-d") . '.log'
            //开启自定义错误日志
            set_error_handler(array('App', "yrError"));
        }

        if (!defined('DEBUG')) define('DEBUG', false);


        //错误信息是否显示
        if (DEBUG) {
            ini_set("display_errors", 1); //显示错误到屏幕
        } else {
            ini_set("display_errors", 0); //隐藏而不显示
        }

        if (isset($_GET['Lang'])) {
            session('Lang', 'en');
        } else {
            if (!session('Lang')) session('Lang', 'en');
        }

        if (isset($_GET['country'])) {
            session('country', strtoupper($_GET['country']));
        } else {
            if (!session('country'))
                session('country', reset(explode(',', $_SERVER["HTTP_ACCEPT_LANGUAGE"])));
        }

        $langPath = APP_PATH . 'Lang/lang_' . session('Lang') . '.php';

        if (file_exists($langPath))
            getLang(require $langPath);


        csrfToken();
        session('_old_input', $_POST);
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

        $log_file = '%s_log_' . date("Y-m-d") . '.log';//定义日志文件名;
        $template = '';

        switch ($errNo) {
            case E_USER_ERROR:
                $template .= "用户ERROR级错误，必须修复 错误编号[$errNo] $errStr ";
                $template .= "错误位置 文件$errFile,第 $errLine 行\n";
                $log_file = sprintf($log_file, 'error');

                break;
            case E_WARNING://运行时警告（非致命的错误）2 
            case E_USER_WARNING:
                $template .= "用户WARNING级错误，建议修复 错误编号[$errNo] $errStr ";
                $template .= "错误位置 文件$errFile,第 $errLine 行\n";
                $log_file = sprintf($log_file, 'warning');
                break;

            case E_NOTICE://运行时注意消息（可能是或者可能不是一个问题） 8
            case E_USER_NOTICE:
                $template .= "用户NOTICE级错误，不影响系统，可不修复 错误编号[$errNo] $errStr ";
                $template .= "错误位置 文件$errFile,第 $errLine 行\n";
                $log_file = sprintf($log_file, 'notice');
                break;

            default:
                $template .= "未知错误类型: 错误编号[$errNo] $errStr  ";
                $template .= "错误位置 文件$errFile,第 $errLine 行\n";
                $log_file = sprintf($log_file, 'unknown');
                break;
        }

        Debug::log($log_file, $template);
        return true;
    }


    static function run()
    {
        self::init();
        self::loadConf();

        $url = self::uri()->rsegment();

        $ctrBaseNamespace = APP_PATH . Config::get('ctrBaseNamespace') . '/';

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

            foreach ($url as $k => $v) {
                $v = ucfirst(strtolower($v));

                if (is_dir($ctrBaseNamespace . $v)) {
                    $ctrBaseNamespace .= empty($v) ? '' : $v . '/';
                    $classObj .= '\\' . $v;
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


        $classObj .= '\\' . $className;

        $nowAction = $className . '/' . $action;

        $classPath = $ctrBaseNamespace . $className . '.php';

        Config::set([
            'classPath' => $classPath,
            'ctlName' => $className,
            'actName' => $action,
            'nowAction' => $nowAction,
            'Lang' => session('Lang')
        ]);

        if (method_exists($classObj, $action)) {

            self::pipeline()
                ->send(self::request())
                ->through(Config::get('middleware.before'))
                ->then(function ($request) use ($classObj, $action, $url) {

                    $ctlObj = self::loadClass($classObj);

                    $middleware = array_merge(Config::get('middleware.middle'), $ctlObj->getMiddleware());

                    self::pipeline()
                        ->send($request)
                        ->through($middleware)
                        ->then(function ($request) use ($classObj, $action, $url) {
                            $request->view = self::runMethod($classObj, $action, $url);

                            self::pipeline()
                                ->send($request)
                                ->through(Config::get('middleware.after'))
                                ->then(function ($request) {
                                    echo $request->view;
                                });

                        });
                });

        } else {
            error404();
        }

        if (DEBUG && !self::request()->isAjax()) {
            echo Debug::message();
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
        //弹出第一个参数，这是类名，剩下的都是要传给实例化类的构造函数的参数了
        $className = array_shift($arguments);
        $key = trim($className, '\\');

        if (!isset(self::$instanceList[$key])) {
            $reflection = new ReflectionClass($className);
            if ($reflection->isInterface()) {
                $reflection = new ReflectionClass(Config::get('interface.' . $className));
            }
            if (!$arguments) {
                $arguments = self::getDependencies($reflection->getConstructor());
            }

            self::$instanceList[$key] = $reflection->newInstanceArgs($arguments);
        }
        return self::$instanceList[$key];
    }

    /**
     * 递归解析参数
     * @param \ReflectionMethod $rfMethod
     * @return array
     * @throws \Exception
     */
    static function getDependencies(\ReflectionMethod $rfMethod, $params = [])
    {
        $instanceParams = [];

        if (!$rfMethod instanceof \ReflectionMethod) return $instanceParams;

        foreach ($rfMethod->getParameters() as $param) {


            if ($dependency = $param->getClass()) {   //该参数不是对象
                $instanceParams[] = self::loadClass($dependency->name);

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

        $args = self::getDependencies($reflectionMethod, $arguments);//返回类方法的参数

        return $reflectionMethod->invokeArgs(self::loadClass($className), $args);
    }

    public static function __callStatic($name, $paramenters)
    {
        $class = Config::get('classAlias.' . $name);
        if (class_exists($class))
            return loadClass($class, $paramenters);
    }
}


App::run();
