<?php
namespace YrPHP;

use App;

abstract class Facade
{
    public static $className;

    public function __call($method, $args)
    {
        $instance = App::loadClass(static::$className);
        return call_user_func_array([$instance, $method], $args);
    }

    public function __get($name)
    {
        $instance = App::loadClass(static::$className);
        return isset($instance->$name) ? $instance->$name : null;
    }

    public static function __callStatic($method, $args)
    {
        $instance = App::loadClass(static::$className);
        return call_user_func_array([$instance, $method], $args);
    }
}