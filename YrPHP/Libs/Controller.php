<?php
/**
 * Created by YrPHP.
 * User: Kwin
 * QQ:284843370
 * Email:kwinwong@hotmail.com
 * GitHub:https://github.com/kwinH/YrPHP
 */
namespace YrPHP;


abstract class Controller extends YrTpl
{
    private static $instance;


    function __construct()
    {
        self::$instance =& $this;

        /*******************构造方法，用于初使化模版对象中的成员属性********************/
        parent::__construct();         //调用父类被覆盖的构造方法
        $this->templateDir = C('setTemplateDir');       //定义模板文件存放的目录
        $this->compileDir = C('setCompileDir');      //定义通过模板引擎组合后文件存放目录
        $this->caching = C('caching');     //缓存开关 1开启，0为关闭
        $this->cacheLifeTime = C('cacheLifetime');  //设置缓存的时间 0代表永久缓存
        $this->cacheDir = C('setCacheDir');      //设置缓存的目录
        $this->leftDelimiter = C('leftDelimiter');          //在模板中嵌入动态数据变量的左定界符号
        $this->rightDelimiter = C('rightDelimiter'); //模板文件中使用的“右”分隔符号

        $this->uri = loadClass('YrPHP\Uri');
        $this->checkCacheId();

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