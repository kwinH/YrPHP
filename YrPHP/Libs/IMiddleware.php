<?php
/**
 * Created by YrPHP.
 * User: Kwin
 * QQ:284843370
 * Email:kwinwong@hotmail.com
 * GitHub:https://github.com/kwinH/YrPHP
 */
namespace YrPHP;
use \Closure;

/**
 * 定义中间件接口,所有中间间都必须实现该接口
 * Interface Middleware
 */
interface IMiddleware{
    /**
     * 中间件执行
     * @param Request $request
     * @return mixed
     */
    public function handler(Request $request,Closure $next);
}