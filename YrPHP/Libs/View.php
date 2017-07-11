<?php
/**
 * Created by YrPHP.
 * User: Kwin
 * QQ:284843370
 * Email:kwinwong@hotmail.com
 * GitHub:https://github.com/kwinH/YrPHP
 */
namespace YrPHP;
class View
{
    protected static $callNumber = 0;    //防止重复调用
    protected $templateDir;  //定义通过模板引擎组合后文件存放目录
    protected $comFileName;  //编译好的模版文件名
    protected $compileDir; //定义编译文件存放目录
    protected $ctlFile;//控制器文件
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

    public $block = [];//section内容块

    public $cacheStatus = true;

    public function __construct()
    {
        $this->setConfig();

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

        $this->tplVars['errors'] = session('errors');
    }

    public function setConfig($config = [])
    {
        if (empty($config)) {
            $this->templateDir = C('setTemplateDir');       //定义模板文件存放的目录
            $this->compileDir = C('setCompileDir');      //定义通过模板引擎组合后文件存放目录
            $this->caching = C('caching');     //缓存开关 1开启，0为关闭
            $this->cacheLifeTime = C('cacheLifetime');  //设置缓存的时间 0代表永久缓存
            $this->cacheDir = C('setCacheDir');      //设置缓存的目录
            $this->leftDelimiter = C('leftDelimiter');          //在模板中嵌入动态数据变量的左定界符号
            $this->rightDelimiter = C('rightDelimiter'); //模板文件中使用的“右”分隔符号

            $this->ctlFile = C('classPath');//控制器文件
            $this->cacheSubDir = C('cacheSubDir', C('ctlName')); //定义生成的缓存文件的子目录默认为控制器名
            $this->cacheFileName = C('cacheFileName', C('actName'));//定义生成的缓存文件名 默认为方法名
        } else {
            foreach ($config as $k => $v) {
                $this->$k = $v;
            }
        }
        return $this;
    }

    /**
     * 将PHP中分配的值会保存到成员属性$tplVars中，用于将板中对应的变量进行替换
     * @param    string $tpl_var 需要一个字符串参数作为关联数组下标，要和模板中的变量名对应
     * @param    mixed $value 需要一个标量类型的值，用来分配给模板中变量的值
     */
    public function assign($tplVar, $value = null)
    {
        if ($tplVar != '') {
            $this->tplVars[$tplVar] = $value;
        }
    }

    /**
     * 加载指定目录下的模板文件，并将替换后的内容生成组合文件存放到另一个指定目录下
     * @param    string $fileName 提供模板文件的文件名
     * @param    array $tpl_var 需要一个字符串参数作为关联数组下标，要和模板中的变量名对应
     * @param    string 当$cacheId为false时，不会生成缓存文件，其他情况做为缓存ID,当有个文件有多个缓存时，$cacheId不能为空，否则会重复覆盖
     */
    public function display($fileName, $tplVars = '', $cacheId = '')
    {
        //缓存静态文件
        $this->init($cacheId);

        $this->buildTplFile($fileName, $tplVars);
        $this->blockExtends();

        extract($this->tplVars);
        require $this->comFileName;

        $this->cacheContent = ob_get_contents();

        // ob_end_flush();
        ob_end_clean();

        $this->cacheStatus = $cacheId;

        return $this->cacheContent;
    }


    public function buildTplFile($fileName, $tplVars = '')
    {
        if (!empty($tplVars)) {
            $this->tplVars = array_merge($this->tplVars, $tplVars);
        }

        /* 到指定的目录中寻找模板文件 */
        $fileName = strpos($fileName, '.') !== false ? $fileName : ($fileName . '.' . C('templateExt'));
        $tplFile = $this->templateDir . $fileName;

        /* 如果需要处理的模板文件不存在,则退出并报告错误 */
        if (!file_exists($tplFile)) {
            throw new Exception("模板文件{$tplFile}不存在！");
        }

        /* 获取组合的模板文件，该文件中的内容都是被替换过的 */
        $comFileDir = $this->compileDir . C('ctlName') . (strpos($fileName, '/') === false ? '' : '/' . dirname($fileName));

        if (!file_exists($comFileDir)) {
            File::mkDir($comFileDir);
        }

        $tempComFileName = $comFileDir . '/' . basename($fileName) . '.php';

        if (is_null($this->comFileName)) {
            $this->comFileName = $tempComFileName;
        }

        if (!file_exists($tempComFileName) || filemtime($tempComFileName) < filemtime($tplFile) || filemtime($tempComFileName) < filemtime($this->ctlFile)) {
            $repContent = $this->tplReplace(file_get_contents($tplFile));

            $this->setBlock($repContent);

            /* 保存由系统组合后的脚本文件 */
            file_put_contents($tempComFileName, $repContent);
            return $repContent;
        }

        return file_get_contents($tempComFileName);

    }


    public function getComFileName($fileName)
    {
        /* 到指定的目录中寻找模板文件 */
        $fileName = strpos($fileName, '.') !== false ? $fileName : ($fileName . '.' . C('templateExt'));

        /* 获取组合的模板文件，该文件中的内容都是被替换过的 */
        $this->comFileName = $this->compileDir . C('ctlName') . '/' . $fileName . '.php';

        return $this->comFileName;
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
        $this->rule['/' . $this->leftDelimiter . 'assign\s+(.*)\s*=\s*(.*)' . $this->rightDelimiter . '/isU'] = "<?php \\1 = \\2;?>";//分配变量
        $this->rule['/' . $this->leftDelimiter . '(break|continue)\s*' . $this->rightDelimiter . '/isU'] = "<?php \\1;?>";//跳出循环
        $this->rule['/' . $this->leftDelimiter . '(\$.*)(\+\+|\-\-)\s*' . $this->rightDelimiter . '/isU'] = "<?php \\1\\2;?>";//运算
        $this->rule['/' . $this->leftDelimiter . '(\+\+|\-\-)(\$.*)\s*' . $this->rightDelimiter . '/isU'] = "<?php \\1\\2;?>";//运算


        $content = preg_replace(array_keys($this->rule), array_values($this->rule), $content);

        //变量替换
        foreach ($this->tplVars as $key => $value) {
            $content = preg_replace('/\$(' . $key . ')/', '$\\1', $content);
        }

        //包含标签
        return preg_replace_callback('/' . $this->leftDelimiter . '(include|require)\s+(.*)\s*' . $this->rightDelimiter . '/isU',
            function ($matches) {
                return $this->buildTplFile($matches[2], null);
            },
            $content);
    }

    public function setBlock($data)
    {
        preg_match_all('/' . $this->leftDelimiter . 'section\s+(.*)\s*' . $this->rightDelimiter . '(.*)' .
            $this->leftDelimiter . 'endsection' . $this->rightDelimiter . '/isU', $data, $matches);

        foreach ($matches[1] as $k => $v) {
            $this->block[$v] = $matches[2][$k];
        }
    }


    protected function blockExtends()
    {

        $data = file_get_contents($this->comFileName);

        preg_match('/' . $this->leftDelimiter . 'extends\s+(.*)' . $this->rightDelimiter . '/isU', $data, $matches);

        if (!empty($matches[1])) {
            $data = $this->buildTplFile($matches[1]);


            $data = preg_replace_callback(
                '/' . $this->leftDelimiter . 'yield\s+(.*)' . $this->rightDelimiter . '/isU',
                function ($matches) {
                    return $this->block[$matches[1]];
                },
                $data
            );

            file_put_contents($this->comFileName, $data);
        }
    }

    /**
     * 静态化
     * @param    string $cacheId 缓存ID 当有个文件有多个缓存时，$cacheId不能为空，否则会重复覆盖
     */
    public function init($cacheId = '')
    {
        ob_start();
        if (static::$callNumber) {
            return false;
        }

        if ($this->caching) {
            //static::$cacheId[] = $cacheId;
            $cacheDir = rtrim($this->cacheDir, '/') . '/' . $this->cacheSubDir;

            if (!file_exists($cacheDir)) {
                mkdir($cacheDir, 0755);
            }

            $this->cacheFile = $cacheDir . '/' . $this->cacheFileName;
            $this->cacheFile .= empty($cacheId) ? '' : '_' . $cacheId;
            $this->cacheFile .= '.html';

            if (file_exists($this->cacheFile)) {
                $cacheFileMTime = filemtime($this->cacheFile);
                if (file_exists($this->cacheFile) && $cacheFileMTime > filemtime($this->ctlFile) && $cacheFileMTime + $this->cacheLifeTime > time()) {
                    requireCache($this->cacheFile);
                    exit;
                }
                static::$callNumber++;
            }
        }

    }

    /**
     *  生成静态文件
     */
    protected function setCache()
    {
        if ($this->cacheStatus === false) {
            return '';
        }

        if (file_exists($this->comFileName) && $this->caching) {
            if (
                !file_exists($this->cacheFile) || ($this->cacheLifeTime != 0
                    && filemtime($this->cacheFile) + $this->cacheLifeTime < time())
            ) {
                File::vi($this->cacheFile, $this->cacheContent);
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
                        if (is_dir($subFile)) {                    //如果是目录条件则成立
                            $this->delDir($subFile);                //递归调用自己删除子目录
                        } else if (is_file($subFile)) {
                            //如果是文件条件则成立
                            if (
                                empty($template)
                                || (strpos($filename, $template) !== false)
                            ) {
                                unlink($subFile);                    //直接删除这个文件
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


    /**
     * 析构函数 生成缓存文件
     */
    function __destruct()
    {
        $this->setCache();

    }

}

