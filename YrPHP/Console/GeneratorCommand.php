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

    function generate()
    {
        $content = '<?php' . PHP_EOL . 'exit("This file should not be included, only analyzed by your IDE");' . PHP_EOL;
        $classAlias = Config::get('classAlias');
        foreach ($classAlias as $key => $className) {
            $content .= 'class ' . $key . '{' . PHP_EOL;
            $reflection = new \ReflectionClass($className);
            foreach ($reflection->getMethods() as $method) {
                $pars = '';
                foreach ($method->getParameters() as $reflectionParameter) {
                    $pars .= '$' . $reflectionParameter->getName() . ',';
                }
                $content .= $method->getDocComment() . PHP_EOL;
                $content .= 'static function ' . $method->name . '(' . trim($pars, ',') . '){}' . PHP_EOL;
            }
            $content .= '}' . PHP_EOL;

        }
        file_put_contents('.ide_help.php', $content);
    }
}