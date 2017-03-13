<?php
/**
 * Created by YrPHP.
 * User: Kwin
 * QQ:284843370
 * Email:kwinwong@hotmail.com
 * GitHub:https://github.com/kwinH/YrPHP
 * 系统函数库
 */


/**
 * 访问控制器的原始资源
 * 返回当前实例控制器对象
 * $app =& getInstance();
 * @return Controller 资源
 */
function &getInstance()
{
    return YrPHP\Controller::getInstance();
}


/**
 * 获取配置参数
 * @param string|array $name 配置变量
 * @param mixed $default 默认值
 * @return mixed
 */
function C($name = null, $default = null)
{
    return \YrPHP\Config::get($name, $default);
}


/**
 * 根据参数，获取完整url，指定是否带入口文件 REWRITE重写模式下不需要指定
 * @param string $url URL表达式，格式：'[模块/控制器/操作#锚点@域名]?参数1=值1&参数2=值2...'
 * @param bool|true $indexPage 如果是REWRITE重写模式 可以不必理会 否则默认显示index.php
 * @return string
 */
function getUrl($url = '', $indexPage = true)
{
    if (isset($_SERVER['HTTP_HOST']) && preg_match('/^((\[[0-9a-f:]+\])|(\d{1,3}(\.\d{1,3}){3})|[a-z0-9\-\.]+)(:\d+)?$/i', $_SERVER['HTTP_HOST'])) {
        $base_url = (\request::isHttps() ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST']
            . substr($_SERVER['SCRIPT_NAME'], 0, strpos($_SERVER['SCRIPT_NAME'], basename($_SERVER['SCRIPT_FILENAME'])));
    } else {
        $base_url = 'http://localhost/';
    }

    $base_url = trim($base_url, '/');

    if (C('urlType') != 2 && $indexPage) {
        $base_url .= $_SERVER['SCRIPT_NAME'];
    }

    if (!empty($url)) {
        $base_url .= '/' . ltrim($url, '/');
    }

    return $base_url;
}

/**
 * 根据参数，获取完整url，不带入口文件
 * @param string $url
 * @return string
 */
function baseUrl($url = '')
{
    return getUrl($url, false);
}


/**
 *  获取语言 支持批量定义
 * @param null $key 语言关键词
 * @param null $value 配置值
 * @return array|null
 */
function getLang($key = null, $value = null)
{
    static $lang = array();
    // 批量设置
    if (is_array($key)) {
        $lang = array_merge($lang, $key);
        return $lang;
    }
    if (!empty($key) && !empty($value)) $lang[$key] = $value;
    if (empty($key)) return '';
    //if (empty($key)) return $Lang;

    if (isset($lang[$key])) return $lang[$key];
    return $key;

}


/**
 * loadClass($className [, mixed $parameter [, mixed $... ]])
 * @param $className 需要得到单例对象的类名
 * @param $parameter $args 0个或者更多的参数，做为类实例化的参数。
 * @return  object
 */
function loadClass()
{
    return call_user_func_array(['App', 'loadClass'], func_get_args());
}


/**
 * 导入辅助函数的文件
 * @param $fileName
 */
function loadHelper($fileName)
{
    $filePath = APP_PATH . 'Helpers/' . $fileName . '.php';

    if (file_exists($filePath)) requireCache($filePath);
}

/**
 * 如果存在自定义的模型类，则实例化自定义模型类，如果不存在，则会实例化Model基类,同时对于已实例化过的模型，不会重复去实例化。
 * @param string $modelName 模型类名
 * @return YrPHP\Model
 */
function M($modelName = "")
{
    $modelClass = APP . '\\' . C('modelBaseNamespace') . '\\' . str_replace(['/', '.'], '\\', $modelName);
    if (class_exists($modelClass)) {
        return App::loadClass($modelClass);
    }
    return App::loadClass('YrPHP\Model', parseNaming($modelName, 2));
}


/**
 * 管理session
 * @param string|array $key
 * @param string $val
 * @return bool
 */
function session($key = '', $val = '')
{
    if (is_null($key)) {
        \YrPHP\Session::destroy();
        return true;
    }

    if (is_null($val)) {
        \YrPHP\Session::delete($key);
        return true;
    }

    if (!empty($val)) {
        \YrPHP\Session::set($key, $val);
        return true;
    }

    if (!empty($key)) {
        return \YrPHP\Session::get($key);
    }

    return \YrPHP\Session::all();
}

/**
 * 管理cookie
 * @param string $key
 * @param string $val
 * @return bool
 */
function cookie($key = '', $val = '')
{

    /**
     * setcookie(name, value, expire, path, domain);
     *  $_COOKIE["user"];
     * setcookie("user", "", time()-3600);
     */
    if (is_null($key)) {

        foreach ($_COOKIE as $k => $v) {
            setcookie($k, "", time() - 3600);
        }

        return true;
    }

    if (is_array($key)) {
        foreach ($key as $k => $v) {
            $_SESSION[$k] = $v;
        }
        return true;
    }

    if (is_null($val)) {

        setcookie($key, "", time() - 3600);
        return true;
    }

    if (!empty($val)) {

        setcookie($key, $val, time() + C('cookieExpire'), C('cookiePath'), C('cookieDomain'));
        return true;
    }

    if (!empty($key)) {

        return $_COOKIE[$key];
    }

    return $_COOKIE;
}


/**
 * 定义一个用来序列化对象的函数
 * 判断配置中的cacheCompress的值是否启动压缩
 * @param $obj
 * @return string
 */
function mySerialize($obj = '')
{
    if (empty($obj)) return false;
    $data = serialize($obj);

    if (C('cacheCompress')) {
        $data = gzcompress($data, 6);
    }
    return $data;
}

/**
 * 反序列化
 * @param $txt
 * @return mixed
 */
function myUnSerialize($txt = '')
{
    if (empty($txt)) return false;
    if (C('cacheCompress')) {
        $txt = gzuncompress($txt);
    }

    return unserialize($txt);
}


/**
 * 优化的require_once
 * @param string $filename 文件地址
 * @return boolean
 */
function requireCache($filename)
{
    static $_importFiles = array();
    if (!isset($_importFiles[$filename])) {
        if (file_exists($filename)) {
            $_importFiles[$filename] = require $filename;;
        } else {
            $_importFiles[$filename] = false;
        }
    }
    return $_importFiles[$filename];
}

/**
 * 404跳转
 * @param string $msg 提示字符串
 * @param string $url 跳转URL
 * @param int $time 指定时间跳转
 */
function error404($msg = '', $url = '', $time = 3)
{
    sendHttpStatus(404);

    $msg = empty($msg) ? '你访问的页面不存在或被删除！' : $msg;

    $url = empty($url) ? getUrl() : $url;

    require BASE_PATH . 'resource/tpl/404.php';
    die;
}

/**
 * 下载一个远程文件到客户端
 * 例  clientDown('http://img.bizhi.sogou.com/images/2012/02/13/66899.jpg');
 * @param $url 一个远程文件
 * @return bool
 */
function clientDown($url)
{
    if (empty($url)) return false;

    $fileName = basename($url);
    ob_start();
    ob_clean();

    if (function_exists('curl_init')) {
        $ch = curl_init();
        $timeout = 5;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $content = curl_exec($ch);
        curl_close($ch);
    } else {
        $content = file_get_contents($url);
    }

    echo $content;

    //file_put_contents($fileName, $content);//保存到服务器
    header('Content-Description: File Download');
    header('Content-type: App.octet-stream');
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header('Content-Length: ' . ob_get_length());
    header('Content-Disposition: attachment; filename=' . $fileName);

}

/**
 * 获取某个月第一天与最后一天的时间戳
 * @param  [type] $month [description]
 * @param  string $year [description]
 * @return [type]        [description]
 */
function getMonthTime($month, $year = '')
{
    if (empty($year)) $year = date('Y');

    $date['firstDay'] = strtotime($year . '-' . $month . '-1');
    $date['firstDayFormat'] = date('Y-m-d', $date['firstDay']);
    $date['lastDay'] = strtotime($date['firstDayFormat'] . '+1 month') - 1;
    $date['lastDayFormat'] = date('Y-m-d', $date['lastDay']);
    return $date;
}

/**
 * http://www.php100.com/html/php/lei/2013/0904/3819.html
 * 获取客户端真实IP
 * @return mixed
 */
function getClientIp()
{
    $unknown = 'unknown';
    if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])
        && $_SERVER['HTTP_X_FORWARDED_FOR']
        && strcasecmp($_SERVER['HTTP_X_FORWARDED_FOR'],
            $unknown)
    ) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } elseif (isset($_SERVER['REMOTE_ADDR'])
        && $_SERVER['REMOTE_ADDR'] &&
        strcasecmp($_SERVER['REMOTE_ADDR'], $unknown)
    ) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    /*
    处理多层代理的情况
    或者使用正则方式：$ip = preg_match("/[d.]
    {7,15}/", $ip, $matches) ? $matches[0] : $unknown;
    */
    if (false !== strpos($ip, ','))
        $ip = reset(explode(',', $ip));

    return $ip;
}


/**
 * //新浪根据IP获得地址
 * @param string $ip
 * @return mixed|string
 * array ( 'ret' => 1, 'start' => -1, 'end' => -1, 'country' => '中国', 'province' => '浙江', 'city' => '杭州', 'district' => '', 'isp' => '', 'type' => '', 'desc' => '', )
 */
function Ip2Area($ip = '')
{
    $ip = empty($ip) ? getClientIp() : $ip;
    $ch = curl_init();
    $options[CURLOPT_URL] = 'http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=json&ip=' . $ip;
    $options[CURLOPT_RETURNTRANSFER] = true;
    curl_setopt_array($ch, $options);
    $re = curl_exec($ch);
    $area = json_decode($re, true);
    $area['ip'] = $ip;
    if (!is_array($area) || $area['ret'] == -1) return false;//'未知地区'
    return $area;
    return $area['country'] . '  ' . $area['province'] . '  ' . $area['city'];

}

/**
 * 生成随机字符
 * @param int $len
 * @param string $type w：英文字符 d：数字 wd: dw:数字加英文字符
 * @return string
 */
function randStr($len = 8, $type = 'wd')
{
    $type = strtolower($type);

    switch ($type) {
        case 'w':
            $pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            break;
        case 'dw':
        case 'wd':
            $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            break;
        case 'd':
            $pool = '0123456789';
            break;
        default:
            //$pool = uniqid();
            $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ~!@#$%^&*()_+-={}\\|:;\'",.?/';
            break;
    }

    return substr(str_shuffle(str_repeat($pool, ceil($len / strlen($pool)))), 0, $len);

}

/**
 * 发送HTTP状态
 * @param integer $code 状态码
 * @return void
 */
function sendHttpStatus($code)
{
    \Response::status($code);
}

/**
 * 页面跳转
 * @param string $url
 */
function gotoUrl($url = '')
{
    if (is_null($url)) $url = getUrl();
    header('Location:'.$url);
    die;
}


/**
 * 数据脱敏处理隐私数据的安全保护
 * @param string $str
 * @param int $start
 * @param int $length
 * @param string $replacement
 * @return mixed
 */
function desensitize($str = '', $start = 0, $length = 0, $replacement = '*')
{
    $strLen = mb_strlen($str);

    $end = -($strLen - ($start + $length));

    $length = $length > $strLen ? $strLen - $start : $length;

    $replacement = $length > 0 ? str_repeat($replacement, $length) : '';

    return mb_substr($str, 0, $start) . $replacement . mb_substr($str, $end);

}

/**
 * 返回一个旧的输入值
 * @param string $inputName
 * @param null $default
 * @return string|null
 */
function old($inputName = '', $default = null)
{
    if (!$oldInput = YrPHP\Session::get('_old_input')) return $default;

    if (empty($inputName)) return $oldInput;

    return isset($oldInput[$inputName]) ? $oldInput[$inputName] : $default;

}


/**
 * CSRF Token，该Token可用于验证登录用户和发起请求者是否是同一人，如果不是则请求失败。
 * @return bool|string
 */
function csrfToken()
{
    if (!$token = YrPHP\Session::get('_token'))
        $token = randStr(32);

    YrPHP\Session::set('_token', $token);

    return $token;
}

/**
 * 生成一个包含CSRF Token值的隐藏域
 * @return string
 */
function csrfField()
{
    $csrfToken = csrfToken();

    $html = <<< HTML
<input type="hidden" name="_token" value="{$csrfToken}">
HTML;

    return $html;
}


/**
 * 命名规则转换
 * @param string $name
 * @param int $type 0、小驼峰法、1、大驼峰法、2、蛇形命名法
 * @return mixed|string
 */
function parseNaming($name = '', $type = 0)
{
    switch ($type) {
        case 0:
            return preg_replace_callback('/_([a-zA-Z])/', function ($match) {
                return strtoupper($match[1]);
            }, $name);
            break;
        case 1:
            return ucfirst(preg_replace_callback('/_([a-zA-Z])/', function ($match) {
                return strtoupper($match[1]);
            }, $name));
            break;
        case 2:
            return strtolower(trim(preg_replace("/[A-Z]/", "_\\0", $name), "_"));
            break;
    }

}
