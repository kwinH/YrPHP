<?php

/**
 * Created by YrPHP.
 * User: Kwin
 * QQ:284843370
 * Email:kwinwong@hotmail.com
 * GitHub:https://github.com/kwinH/YrPHP
 */

namespace YrPHP\Console;

use YrPHP\Config;

class GeneratorCommand
{

    /**
     * php index.php ide-help generate
     */
    function generate()
    {
        $content = '<?php' . PHP_EOL . 'exit("This file should not be included, only analyzed by your IDE");' . PHP_EOL;
        $classAlias = Config::get('classAlias');
        foreach ($classAlias as $key => $className) {
            $content .= 'class ' . $key . '{' . PHP_EOL;
            $reflection = new \ReflectionClass($className);
            foreach ($reflection->getMethods() as $method) {
                if (strpos($method->name, '__') === 0) {
                    continue;
                }
                $args = [];
                $pars = '';
                foreach ($method->getParameters() as $k => $reflectionParameter) {
                    $args[$k] = '$' . $reflectionParameter->getName();
                    $pars .= $args[$k];
                    if ($reflectionParameter->isDefaultValueAvailable()) {
                        $defaultValue = $reflectionParameter->getDefaultValue();
                        $pars .= '=' . preg_replace('/\s*/', '', var_export($defaultValue, true));
                    }
                    $pars .= ',';
                }
                $content .= $method->getDocComment() . PHP_EOL;
                $content .= 'static function ' . $method->name . '(' . trim($pars, ',') . '){return ' . $className . '::' . $method->name . '(' . implode(',', $args) . ');}' . PHP_EOL;
            }
            $content .= '}' . PHP_EOL;

        }
        file_put_contents('.ide_help.php', $content);
    }

    /**
     * php index.php ide-help classAlias
     */
    function classAlias()
    {
        $classAlias = Config::get('classAlias');
        $file = '<?php' . PHP_EOL . 'use YrPHP\Facade;';
        foreach ($classAlias as $alias => $original) {
            $file .= PHP_EOL . 'class ' . ucfirst(strtolower($alias)) . ' extends Facade{public static $className=\'' . $original . '\';}';
        }
        file_put_contents('_class_alias.php', $file);
    }
}