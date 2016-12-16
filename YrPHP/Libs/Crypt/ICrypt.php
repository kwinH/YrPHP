<?php
/**
 * Created by YrPHP.
 * User: Kwin
 * QQ: 284843370
 * Email: kwinwong@hotmail.com
 * GitHub: https://github.com/kwinH/YrPHP
 */
namespace YrPHP\Crypt;

interface   ICrypt
{
    public function encrypt($input);

    public function decrypt($encrypted);
}