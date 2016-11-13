<?php
/**
 * 自动生成目录结构
 * Created by YrPHP.
 * User: Kwin
 * QQ: 284843370
 * Email: kwinwong@hotmail.com
 * GitHub: https://GitHubhub.com/quinnfox/yrphp
 *
 */
namespace YrPHP\Core;
use YrPHP\Libs\File;

class Structure
{

    static function run()
    {

        $fun = <<<st
<?php
/**
 * 自定义函数库
 * Created by YrPHP.
 * User: Kwin
 * QQ:284843370
 * Email:kwinwong@hotmail.com
 */

st;

        $controls = <<<st
<?php
/**
 * Created by YrPHP.
 * User: Kwin
 * QQ:284843370
 * Email:kwinwong@hotmail.com
 */
use Core\Controller;

class index extends Controller
{
    function __construct()
    {
        parent::__construct();

    }


    function  index()
    {
    echo "<h1>欢迎使用yrPHP 有什么建议或则问题 请随时联系我<br/>QQ：284843370<br/>email:kwinwong@hotmail.com</h1>";
    }
    }
st;


        $html = <<<st
<!DOCTYPE html>
<html>
<head>
	<title>403 Forbidden</title>
</head>
<body>

<p>Directory access is forbidden.</p>

</body>
</html>
st;


        $phpReturn = <<<st
<?php
/**
 * Created by YrPHP.
 * User: Kwin
 * QQ:284843370
 * Email:kwinwong@hotmail.com
 */

return array(

            );
st;






        $path = array(
            APP_PATH . 'Common/index.html'              => $html,
            APP_PATH . 'Common/functions.php'           => $fun,
            APP_PATH . 'Config/index.html'              => $html,
            APP_PATH . 'Controllers/index.html'            => $html,
            APP_PATH . 'Controllers/Index.php'       => $controls,
            APP_PATH . 'Core/index.html'                => $html,
            APP_PATH . 'Lang/index.html'                => $html,
            APP_PATH . 'Lang/lang_cn.php'               => $phpReturn,
            APP_PATH . 'Libs/index.html'                => $html,
            APP_PATH . 'Models/index.html'              => $html,
            APP_PATH . 'runtime/index.html'             => $html,
            APP_PATH . 'runtime/cache/index.html'       => $html,
            APP_PATH . 'runtime/session/index.html'       => $html,
            APP_PATH . 'runtime/compile_tpl/index.html' => $html,
            APP_PATH . 'runtime/data/index.html'        => $html,
            APP_PATH . 'runtime/logs/index.html'        => $html,
            APP_PATH . 'views/index.html'               => $html,
        );


        foreach ($path as $k => $v) {
            File::vi($k, $v);
        }
        File::cp(BASE_PATH.'Config',APP_PATH.'Config');
        File::mkDir(ROOT_PATH.'public');
        header("Location: " . getUrl());

    }
}