<?php
/**
 * Created by YrPHP.
 * User: Kwin
 * QQ:284843370
 * Email:kwinwong@hotmail.com
 * GitHub:https://github.com/kwinH/YrPHP
 */
namespace YrPHP;
class YrTpl
{
    private static $callNumber = 0;    //防止重复调用
    protected $templateDir;  //定义通过模板引擎组合后文件存放目录
    protected $comFileName;  //编译好的模版文件名
    protected $compileDir; //定义编译文件存放目录

    protected $caching = true;   //bool 设置缓存是否开启
    protected $cacheLifeTime = 3600;  //定义缓存时间
    protected $cacheDir;      //定义生成的缓存文件路径
    protected $cacheSubDir;   //定义生成的缓存文件的子目录默认为控制器名
    protected $cacheFileName; //定义生成的缓存文件名 默认为方法名
    private $cacheFile;      //最后形成的缓存完整路径
    private $cacheContent = '';//缓存内容

    protected $leftDelimiter = '{';   //在模板中嵌入动态数据变量的左定界符号
    protected $rightDelimiter = '}'; //在模板中嵌入动态数据变量的右定界符号
    protected $rule = array();//替换搜索的模式的数组 array(搜索的模式 => 用于替换的字符串 )
    private $tplVars = array(); //内部使用的临时变量


    public function __construct()
    {

        $this->ctlFile = C('classPath');//控制器文件
        $this->cacheSubDir = C('ctlName');
        $this->cacheFileName = C('actName');

        $this->leftDelimiter = preg_quote($this->leftDelimiter, '/');//转义正则表达式字符
        $this->rightDelimiter = preg_quote($this->rightDelimiter, '/');//转义正则表达式字符

        if (file_exists(APP_PATH . 'Config/tabLib.php')) {
            $this->rule = require APP_PATH . 'Config/tabLib.php';
            if (is_array($this->rule)) {
                foreach ($this->rule as $k => $v) {
                    $this->rule['/' . $this->leftDelimiter . $k . $this->rightDelimiter . '/isU'] = $v;
                    unset($this->rule[$k]);
                }
            } else {
                unset($this->rule);
                $this->rule = array();
            }
        }


    }

    /**
     * 将PHP中分配的值会保存到成员属性$tplVars中，用于将板中对应的变量进行替换
     * @param    string $tpl_var 需要一个字符串参数作为关联数组下标，要和模板中的变量名对应
     * @param    mixed $value 需要一个标量类型的值，用来分配给模板中变量的值
     */
    function assign($tplVar, $value = null)
    {
        if ($tplVar != '')
            $this->tplVars[$tplVar] = $value;
    }

    /**
     * 加载指定目录下的模板文件，并将替换后的内容生成组合文件存放到另一个指定目录下
     * @param    string $fileName 提供模板文件的文件名
     * @param    array $tpl_var 需要一个字符串参数作为关联数组下标，要和模板中的变量名对应
     * @param    string $cacheId 缓存ID 当有个文件有多个缓存时，$cacheId不能为空，否则会重复覆盖
     */
    function display($fileName, $tplVars = '', $cacheId = '')
    {
        //缓存静态文件
        $this->init($cacheId);

        if (!empty($tplVars)) $this->tplVars = array_merge($this->tplVars, $tplVars);

        extract($this->tplVars);

        /* 到指定的目录中寻找模板文件 */
        $fileName = strpos($fileName, '.') !== false ? $fileName : ($fileName . '.' . C('templateExt'));
        $tplFile = $this->templateDir . $fileName;

        /* 如果需要处理的模板文件不存在,则退出并报告错误 */
        if (!file_exists($tplFile)) die("模板文件{$tplFile}不存在！");

        /* 获取组合的模板文件，该文件中的内容都是被替换过的 */
        $comFileDir = $this->compileDir . C('ctlName');

        if (!file_exists($comFileDir)) mkdir($comFileDir, 0755);

        $this->comFileName = $comFileDir . '/' . $fileName . '.php';


        if (!file_exists($this->comFileName) || filemtime($this->comFileName) < filemtime($tplFile) || filemtime($this->comFileName) < filemtime($this->ctlFile)) {
            $repContent = $this->tplReplace(file_get_contents($tplFile));
            /* 保存由系统组合后的脚本文件 */
            file_put_contents($this->comFileName, $repContent);
        }
        /* 包含处理后的模板文件输出给客户端 */

        require($this->comFileName);

        $this->cacheContent = ob_get_contents();

        // ob_end_flush();
        ob_end_clean();

        if ($cacheId === true) {
            return $this->cacheContent;
        } else {
            echo $this->cacheContent;
        }

    }

    /**
     * 静态化
     * @param    string $cacheId 缓存ID 当有个文件有多个缓存时，$cacheId不能为空，否则会重复覆盖
     */
    public function init($cacheId = '')
    {
        ob_start();
        if (self::$callNumber) return;

        if ($this->caching) {
            //self::$cacheId[] = $cacheId;
            $cacheDir = rtrim($this->cacheDir, '/') . '/' . $this->cacheSubDir;

            if (!file_exists($cacheDir)) mkdir($cacheDir, 0755);

            $this->cacheFile = $cacheDir . '/' . $this->cacheFileName;
            $this->cacheFile .= empty($cacheId) ? '' : '_' . $cacheId;
            $this->cacheFile .= '.html';

            if (file_exists($this->cacheFile)) {
                $cacheFileMTime = filemtime($this->cacheFile);
                if (file_exists($this->cacheFile) && $cacheFileMTime > filemtime($this->ctlFile) && $cacheFileMTime + $this->cacheLifeTime > time()) {
                    requireCache($this->cacheFile);
                    exit;
                }
                self::$callNumber++;
            }
        }
    }

    private function tplReplace($content)
    {

        $this->rule['/' . $this->leftDelimiter . '=(.*)\s*' . $this->rightDelimiter . '/isU'] = "<?php echo \\1;?>";//输出变量、常量或函数
        $this->rule['/' . $this->leftDelimiter . 'foreach\s*\((.*)\)\s*' . $this->rightDelimiter . '/isU'] = "<?php foreach(\\1){?>";//foreach
        $this->rule['/' . $this->leftDelimiter . 'loop\s*\$(.*)\s*' . $this->rightDelimiter . '/isU'] = "<?php foreach(\$\\1 as \$k=>\$v){?>";//loop
        $this->rule['/' . $this->leftDelimiter . 'while\s*\((.*)\)\s*' . $this->rightDelimiter . '/isU'] = "<?php while(\\1){?>";//while
        $this->rule['/' . $this->leftDelimiter . 'for\s*\((.*)\)\s*' . $this->rightDelimiter . '/isU'] = "<?php for(\\1){ ?>";//for
        $this->rule['/' . $this->leftDelimiter . 'if\s*\((.*)\)\s*' . $this->rightDelimiter . '/isU'] = "<?php if(\\1){?>\n";//判断 if
        $this->rule['/' . $this->leftDelimiter . 'else\s*if\s*\((.*)\)\s*' . $this->rightDelimiter . '/'] = "<?php }else if(\\1){?>";//判断 ifelse
        $this->rule['/' . $this->leftDelimiter . 'else\s*' . $this->rightDelimiter . '/'] = "<?php }else{?>";//判断 else
        $this->rule['/' . $this->leftDelimiter . '(\/foreach|\/for|\/while|\/if|\/loop)\s*' . $this->rightDelimiter . '/isU'] = "<?php } ?>";//end
        $this->rule['/' . $this->leftDelimiter . '(include|require)\s+(.*)\s*' . $this->rightDelimiter . '/isU'] = "<?php \$this->display('\\2');?>";//包含标签
        $this->rule['/' . $this->leftDelimiter . 'assign\s+(.*)\s*=\s*(.*)' . $this->rightDelimiter . '/isU'] = "<?php \\1 = \\2;?>";//分配变量
        $this->rule['/' . $this->leftDelimiter . '(break|continue)\s*' . $this->rightDelimiter . '/isU'] = "<?php \\1;?>";//跳出循环
        $this->rule['/' . $this->leftDelimiter . '(\$.*|\+\+|\-\-)(\+\+|\-\-|\$.*)\s*' . $this->rightDelimiter . '/isU'] = "<?php \\1\\2;?>";//运算


        $content = preg_replace(array_keys($this->rule), array_values($this->rule), $content);

        //变量替换
        foreach ($this->tplVars as $key => $value) {
            $content = preg_replace('/\$(' . $key . ')/', '$\\1', $content);
        }
        return $content;

    }


    /**
     *  生成静态文件
     */
    protected function setCache()
    {
        if (file_exists($this->comFileName) && $this->caching) {
            if (!file_exists($this->cacheFile)) {
                file_put_contents($this->cacheFile, $this->cacheContent);
            } elseif ($this->cacheLifeTime != 0 && filemtime($this->cacheFile) + $this->cacheLifeTime < time()) {
                file_put_contents($this->cacheFile, $this->cacheContent);
            }
        }


    }


    /**
     * 清空缓存 默认清空所以缓存
     * @param    string $template 当$file为目录时 清除指定模版（类名_方法）
     * @param    string $cacheId 清除指定模版ID
     */
    protected function clearCache($template = '', $cacheId = '')
    {
        if (empty($cacheId)) {
            return $this->delDir($this->cacheDir, $template);
        } else {
            return unlink($this->cacheDir . $template . '_' . $cacheId . '.html');
        }
    }

    /**
     * 清空文件夹 默认清空所有文件
     * @param    string $file 目录或则目录地址 当是目录时 清空目录内所有文件
     * @param    string $template 当$file为目录时 清除指定模版（类名_方法）
     */
    protected function delDir($file, $template = '')
    {
        if (is_dir($file)) {
            //如果不存在rmdir()函数会出错
            if ($dir_handle = @opendir($file)) {            //打开目录并判断是否成功
                while ($filename = readdir($dir_handle)) {        //循环遍历目录
                    if ($filename != "." && $filename != "..") {    //一定要排除两个特殊的目录
                        $subFile = $file . "/" . $filename;    //将目录下的文件和当前目录相连
                        if (is_dir($subFile))                    //如果是目录条件则成立
                            $this->delDir($subFile);                //递归调用自己删除子目录
                        if (is_file($subFile)) {                //如果是文件条件则成立
                            if (empty($template)) {
                                unlink($subFile);                    //直接删除这个文件
                            } elseif (strpos($filename, $template) !== false) {
                                unlink($subFile);
                            }
                        }
                    }
                }
                closedir($dir_handle);                        //关闭目录资源
                return true;
                //rmdir($file);                     			//删除空目录

            }
        } elseif (is_file($file)) {
            unlink($file);
        }
    }
        public function getMutatedAttributes()
        {
            return $class = get_class($this);
        }

    /**
     * 析构函数 生成缓存文件
     */
    function __destruct()
    {
        $this->setCache();

        if (DEBUG && !isAjaxRequest()) {
            echo Debug::message();
        }
    }

}

