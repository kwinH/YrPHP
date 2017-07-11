<?php
/**
 * Created by YrPHP.
 * User: Kwin
 * QQ: 284843370
 * Email: kwinwong@hotmail.com
 * GitHub: https://github.com/kwinH/YrPHP
 */
namespace YrPHP;


class Crypt
{
    public function __construct()
    {
        $mode = C('cryptMode');
        if ($mode == 'des3') {
            $this->class = loadClass('YrPHP\Crypt\DES3');
        } else {
            throw new Exception('类型错误');
        }
    }

    public function encrypt($input)
    {
        return $this->class->encrypt($input);
    }

    public function decrypt($encrypted)
    {
        return $this->class->decrypt($encrypted);
    }
}