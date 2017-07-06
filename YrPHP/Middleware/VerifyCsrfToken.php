<?php
/**
 * Created by PhpStorm.
 * User: TOYOTA
 * Date: 2016/12/12
 * Time: 15:42
 */

namespace YrPHP\Middleware;

use Closure;
use response;
use YrPHP\IMiddleware;
use YrPHP\Request;

class VerifyCsrfToken implements IMiddleware
{
    protected $excepts = [];

    public function handler(Request $request, Closure $next)
    {
        $token = $request->post('_token');
        $token = $token ? $token : $request->header('X-CSRF-TOKEN');

        $csrfToken = csrfToken();
        if ($request->method() !== 'GET' && !$this->shouldPassThrough($request) && $token != $csrfToken) {

            if ($request->isAjax()) {
                response::json(['error' => 'CSRF验证不通过'], 500);
            } else {
                sendHttpStatus(500);
                require BASE_PATH . 'resource/tpl/csrf_error.php';
            }

            return;
        }

        cookie('XSRF-TOKEN', $csrfToken);

        $next($request);

    }

    protected function shouldPassThrough($request)
    {
        foreach ($this->excepts as $except) {
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