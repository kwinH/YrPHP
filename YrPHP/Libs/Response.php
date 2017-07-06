<?php
/**
 * Created by PhpStorm.
 * User: TOYOTA
 * Date: 2017/3/6 0006
 * Time: 16:29
 */

namespace YrPHP;


class Response
{
    public static $_status = [
        // Informational 1xx
        100 => 'Continue',
        101 => 'Switching Protocols',
        // Success 2xx
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        // Redirection 3xx
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Moved Temporarily ',  // 1.1
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        // 306 is deprecated but reserved
        307 => 'Temporary Redirect',
        // Client Error 4xx
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Request Entity Too Large',
        414 => 'Request-URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Requested Range Not Satisfiable',
        417 => 'Expectation Failed',
        // Server Error 5xx
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        509 => 'Bandwidth Limit Exceeded'
    ];

    public function redirect($url)
    {
        if ($url == 'referer') {
            $url = \Request::referer();
        }

        header('Location: ' . $url);
        exit;
    }

    public function status($code)
    {
        if (isset(static::$_status[$code])) {
            header('HTTP/1.1 ' . $code . ' ' . static::$_status[$code]);
            // 确保FastCGI模式下正常
            header('Status:' . $code . ' ' . static::$_status[$code]);
        }

        return $this;
    }


    protected function sendHeader($headers)
    {
        if (!headers_sent()) {
            foreach ($headers as $key => $value) {
                header($key . ': ' . $value);
            }
        }
    }

    public function json(array $data = [], $code = 200)
    {
        $this->status($code)->sendHeader(['Content-Type' => 'application/json;charset=UTF-8']);
        exit(json_encode($data, JSON_UNESCAPED_UNICODE));
    }

    public function jsonp(array $data = [], $code = 200)
    {
        $this->status($code)->sendHeader(['Content-Type' => 'text/javascript;charset=UTF-8']);

        exit(\request::get('callback', 'callback')
            . '(' . json_encode($data, JSON_UNESCAPED_UNICODE) . ')');
    }

    public function errorBackTo($errors, $url = null)
    {

        Session::flash('_old_input', \Request::all());
        Session::flash('errors', $errors);
        if (\Request::isAjax()) {
            exit($this->json(['error' => $errors]));
        }


        if (\Request::isPost()) {
            if (is_null($url)) {
                $url = 'referer';
            }
        } else {
            if (is_null($url)) {
                $url = \Request::currentUrl();
            }
        }
        $this->redirect($url);
    }

    public function successBackTo($message, $url = null)
    {
        Session::flash('success', $message);

        if (\Request::isAjax()) {
            exit($this->json(['success' => $message]));
        } elseif (\Request::isPost()) {
            if (is_null($url)) {
                $url = 'referer';
            }
        } else {
            if (is_null($url)) {
                $url = \Request::currentUrl();
            }
        }
        $this->redirect($url);
    }

}

