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

    public function redirect($url)
    {
        header('Location: ' . $url);
        exit;
    }

    public function setCode($code)
    {
        $_status = array(
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
        );
        if (isset($_status[$code])) {
            header('HTTP/1.1 ' . $code . ' ' . $_status[$code]);
            // 确保FastCGI模式下正常
            header('Status:' . $code . ' ' . $_status[$code]);
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

    public function toJson(array $data = [], $code = 200)
    {
        $this->setCode($code)->sendHeader(['Content-Type' => ':application/json;charset=UTF-8']);
        exit(json_encode($data, JSON_UNESCAPED_UNICODE));
    }

    public function errorBackTo($errors)
    {
        Session::flash('_old_input', \Request::all());
        session('errors', $errors);
        if (\Request::isAjax()) {
            exit($this->toJson(['error' => $errors]));
        }

        if (\Request::isPost()) {
            $this->redirect(\Request::referer());
        }
    }

}

