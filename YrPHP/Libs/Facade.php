<?php
namespace YrPHP;

use App;
use ReflectionMethod;

abstract class Facade
{
    public static $className;

    public static function __callStatic($method, $args)
    {
        $reflectionMethod = new ReflectionMethod(static::$className, $method);
        if ($reflectionMethod->isStatic()) {
            $className = static::$className;
            switch (count($args)) {
                case 0:
                    return $className::$method();
                case 1:
                    return $className::$method($args[0]);
                case 2:
                    return $className::$method($args[0], $args[1]);
                case 3:
                    return $className::$method($args[0], $args[1], $args[2]);
                case 4:
                    return $className::$method($args[0], $args[1], $args[2], $args[3]);
                default:
                    return call_user_func_array([static::$className, $method], $args);

            }
        } else {
            $instance = App::loadClass(static::$className);
            switch (count($args)) {
                case 0:
                    return $instance->$method();
                case 1:
                    return $instance->$method($args[0]);
                case 2:
                    return $instance->$method($args[0], $args[1]);
                case 3:
                    return $instance->$method($args[0], $args[1], $args[2]);
                case 4:
                    return $instance->$method($args[0], $args[1], $args[2], $args[3]);
                default:
                    return call_user_func_array([$instance, $method], $args);
            }
        }
    }
}