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

class Debug
{
    static $info = array();
    static $queries = array();
    static $startTime;                //保存脚本开始执行时的时间（以微秒的形式保存）
    static $stopTime;                //保存脚本结束执行时的时间（以微秒的形式保存）


    /**
     * 在脚本开始处调用获取脚本开始时间的微秒值
     */
    static function start()
    {
        self::$startTime = microtime(true);   //将获取的时间赋给成员属性$startTime
    }

    /**
     *在脚本结束处调用获取脚本结束时间的微秒值
     */
    static function stop()
    {
        self::$stopTime = microtime(true);   //将获取的时间赋给成员属性$stopTime
    }

    /**
     * 添加调试消息
     * @param    string $msg 调试消息字符串
     * @param    int $type 消息的类型
     */
    static function addMsg($msg, $type = 0)
    {
        if (defined("DEBUG") && DEBUG == 1) {
            switch ($type) {
                case 0:
                    self::$info[] = $msg;
                    break;
                case 1:
                    self::$includeFile[] = $msg;
                    break;
                case 2:
                    self::$queries[] = $msg;
                    break;
            }
        }
    }

    /**
     * 已经实例化的自定义类集合
     * @return array
     */
    static function newClasses()
    {
        $declaredClasses = [];
        foreach (get_declared_classes() as $class) {
            //实例化一个反射类
            $reflectionClass = new \ReflectionClass($class);
            //如果该类是自定义类
            if ($reflectionClass->isUserDefined()) {
                //导出该类信息
                // \Reflection::export($reflectionClass);
                $declaredClasses[] = $class;
            }

        }
        return $declaredClasses;
    }

    /**
     *  返回被 include 和 require 文件名的 array
     * @return array
     */
    static function getIncludedFiles()
    {
        return get_included_files();
    }

    /**
     * 调试时代码高亮显示
     * @param $str
     * @return mixed
     */
    static function highlightCode($str)
    {
        /* The highlight string function encodes and highlights
         * brackets so we need them to start raw.
         *
         * Also replace any existing PHP tags to temporary markers
         * so they don't accidentally break the string out of PHP,
         * and thus, thwart the highlighting.
         */
        $str = str_replace(
            array('&lt;', '&gt;', '<?', '?>', '<%', '%>', '\\', '</script>'),
            array('<', '>', 'phptagopen', 'phptagclose', 'asptagopen', 'asptagclose', 'backslashtmp', 'scriptclose'),
            $str
        );

        // The highlight_string function requires that the text be surrounded
        // by PHP tags, which we will remove later
        $str = highlight_string('<?php ' . $str . ' ?>', TRUE);

        // Remove our artificially added PHP, and the syntax highlighting that came with it
        $str = preg_replace(
            array(
                '/<span style="color: #([A-Z0-9]+)">&lt;\?php(&nbsp;| )/i',
                '/(<span style="color: #[A-Z0-9]+">.*?)\?&gt;<\/span>\n<\/span>\n<\/code>/is',
                '/<span style="color: #[A-Z0-9]+"\><\/span>/i'
            ),
            array(
                '<span style="color: #$1">',
                "$1</span>\n</span>\n</code>",
                ''
            ),
            $str
        );

        // Replace our markers back to PHP tags.
        return str_replace(
            array('phptagopen', 'phptagclose', 'asptagopen', 'asptagclose', 'backslashtmp', 'scriptclose'),
            array('&lt;?', '?&gt;', '&lt;%', '%&gt;', '\\', '&lt;/script&gt;'),
            $str
        );
    }


    /**
     * 输出调试消息
     */
    static function message()
    {

        $mess = "";
        $mess .= '<div style="clear:both;font-size:12px;width:97%;margin:10px;padding:10px;background:#ddd;border:1px solid #009900;z-index:100">';
        $mess .= '<div style="float:left;width:100%;"><span style="float:left;width:200px;"><b>运行信息</b>( <font color="red">' . self::spent(STARTTIME, microtime(true)) . ' </font>秒):</span><span onclick="this.parentNode.parentNode.style.display=\'none\'" style="cursor:pointer;float:right;width:35px;background:#500;border:1px solid #555;color:white">关闭X</span></div><br>';
        $mess .= '<ul style="margin:0px;padding:0 10px 0 10px;list-style:none">';


        self::$info[] = '内存使用：<strong style="color:red">' . round(memory_get_usage() / 1024, 2) . ' KB</strong>';
        self::$info[] = 'URI字符串：' . implode('/', App::uri()->segment());
        self::$info[] = 'URI路由地址：' . implode('/', App::uri()->rsegment());
        self::$info[] = '控制器地址：' . C('classPath');
        self::$info[] = '调用方法：' . C('actName');
        if (count(self::$info) > 0) {
            $mess .= '<br>［系统信息］';
            foreach (self::$info as $info) {
                $mess .= '<li>&nbsp;&nbsp;&nbsp;&nbsp;' . $info . '</li>';
            }
        }
        // Key words we want bolded
        $highlight = array('SELECT', 'DISTINCT', 'FROM', 'WHERE', 'AND', 'LEFT&nbsp;JOIN', 'ORDER&nbsp;BY', 'GROUP&nbsp;BY', 'LIMIT', 'INSERT', 'INTO', 'VALUES', 'UPDATE', 'OR&nbsp;', 'HAVING', 'OFFSET', 'NOT&nbsp;IN', 'IN', 'LIKE', 'NOT&nbsp;LIKE', 'COUNT', 'MAX', 'MIN', 'ON', 'AS', 'AVG', 'SUM', '(', ')');

        $mess .= '<br>［SQL语句］';
        foreach (self::$queries as $val) {
            $sql = self::highlightCode($val['sql']);
            foreach ($highlight as $bold) {
                $sql = str_replace($bold, '<strong>' . $bold . '</strong>', $sql);
            }
            $mess .= '<li style="word-wrap:break-word;word-break:break-all;overflow: hidden;">[' . $val['time'] . ' 秒]&nbsp;&nbsp;&nbsp;&nbsp;' . $sql;

            $mess .= empty($val['error']) ? "" : '<strong style="color: red">Error:</strong>&nbsp;&nbsp;' . $val['error'];
            $mess .= '</li>';
        }

        $mess .= '</ul>';
        $mess .= '</div>';

        return $mess;
    }

    /**
     *返回同一脚本中两次获取时间的差值
     */
    static function spent($startTime = null, $stopTime = null)
    {
        $startTime = empty($startTime) ? self::$startTime : $startTime;
        $stopTime = empty($stopTime) ? self::$stopTime : $stopTime;
        // return round((self::$stopTime - self::$startTime), 4);  //计算后以4舍5入保留4位返回
        return sprintf("%1\$.4f", ($stopTime - $startTime));  //计算后保留4位返回
    }

    /**
     * 记录日志 保存到项目runtime下
     * @param $fileName 文件名
     * @param $content  内容
     */
    static function log($fileName, $content)
    {
        $fileName = C('logDir') . $fileName . '.log';
        file_put_contents($fileName, $content . "\n", FILE_APPEND);
    }
}
