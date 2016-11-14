<?php
/**
 * Created by YrPHP.
 * User: Kwin
 * QQ: 284843370
 * Email: kwinwong@hotmail.com
 * GitHub: https://github.com/kwinH/YrPHP
 */
namespace YrPHP\Libs;


class Crypt
{
    function __construct()
    {
        $mode = C('cryptMode');
        switch ($mode){
            case "des3":
            $this->class = loadClass('YrPHP\Libs\Crypt\DES3');
                break;
            default:
            die('类型错误');
                break;
        }


    }
    function encrypt($input){
    return $this->class->encrypt($input);
    }
    function decrypt($encrypted){
     return   $this->class->decrypt($encrypted);
    }
}