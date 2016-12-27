<?php
/**
 * Created by PhpStorm.
 * User: TOYOTA
 * Date: 2016/12/12
 * Time: 15:42
 */

namespace YrPHP\Middleware;

use Closure;
use YrPHP\IMiddleware;
use YrPHP\Request;
use YrPHP\Session;

class VerifyCsrfToken implements IMiddleware
{
    protected $except = [];

    public function handler(Request $request, Closure $next)
    {

        if ($request->method() !== 'GET' && !$this->shouldPassThrough($request) && $request->post('_token') != csrfToken()) {
            sendHttpStatus(500);
            if ($request->isAjax()) {
                exit($request->toJson(['error' => 'CSRF验证不通过']));
            } else {
                require BASE_PATH . 'resource/tpl/csrf_error.php';
            }

            return;
        }

        $token = randStr('dw', 32);
        Session::set('_token', $token);
        $next($request);

    }

    protected function shouldPassThrough($request)
    {
        foreach ($this->except as $except) {
            if ($except !== '/') {
                $except = trim($except, '/');
            }

            if ($request->is($except)) {
                return true;
            }
        }

        return false;
    }
}