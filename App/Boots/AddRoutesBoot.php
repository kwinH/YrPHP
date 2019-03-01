<?php
/**
 * Project: YrPHP.
 * Author: Kwin
 * QQ:284843370
 * Email:kwinwong@hotmail.com
 */

namespace App\Boots;


use YrPHP\Routing\Router;

class AddRoutesBoot
{

    function init()
    {
        Router::loadRoutesFrom(APP_PATH . 'Config/routes.php');
    }
}