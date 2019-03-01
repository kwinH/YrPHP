<?php
/**
 * Project: swoole.
 * Author: Kwin
 * QQ:284843370
 * Email:kwinwong@hotmail.com
 */

namespace App\Boots;

use YrPHP\Boots\AddMiddleware as BootsAddMiddleware;

class AddMiddleware extends BootsAddMiddleware
{
    /**
     * 全局中间件
     *
     * @var array
     */
    protected $middleware = [
        \YrPHP\Middleware\DebugListen::class,
        \YrPHP\Middleware\VerifyCsrfToken::class,
        \YrPHP\Middleware\DebugShow::class,
    ];


    /**
     * 所有的中间件别名
     *
     * @var array
     */
    protected $routeMiddleware = [];

    /**
     * 所有的中间件组
     * @var array
     */
    protected $middlewareGroups = [];

}