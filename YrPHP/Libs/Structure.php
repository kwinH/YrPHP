<?php
/**
 * 自动生成目录结构
 * Created by YrPHP.
 * User: Kwin
 * QQ: 284843370
 * Email: kwinwong@hotmail.com
 * GitHub: https://github.com/kwinH/YrPHP
 *
 */
namespace YrPHP;

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
namespace App\Controllers;

use YrPHP\Controller;

class Index extends Controller
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
            APP_PATH . 'Helpers/index.html' => $html,
            APP_PATH . 'Helpers/functions.php' => $fun,
            APP_PATH . 'Config/index.html' => $html,
            APP_PATH . 'Controllers/index.html' => $html,
            APP_PATH . 'Controllers/Index.php' => $controls,
            APP_PATH . 'Lang/index.html' => $html,
            APP_PATH . 'Lang/lang_cn.php' => $phpReturn,
            APP_PATH . 'Libs/index.html' => $html,
            APP_PATH . 'Models/index.html' => $html,
            APP_PATH . 'Runtime/index.html' => $html,
            APP_PATH . 'Runtime/Cache/index.html' => $html,
            APP_PATH . 'Runtime/Session/index.html' => $html,
            APP_PATH . 'Runtime/CompileTpl/index.html' => $html,
            APP_PATH . 'Runtime/Data/index.html' => $html,
            APP_PATH . 'Runtime/Logs/index.html' => $html,
            APP_PATH . 'views/index.html' => $html,
            APP_PATH . 'Middleware/index.html' => $html,
        );


        foreach ($path as $k => $v) {
            File::vi($k, $v);
        }
        File::cp(BASE_PATH . 'Config', APP_PATH . 'Config');
        File::mkDir(ROOT_PATH . 'public');
        header("Location: /" );

    }
}