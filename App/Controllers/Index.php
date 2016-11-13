<?php
/**
 * Created by YrPHP.
 * User: Kwin
 * QQ:284843370
 * Email:kwinwong@hotmail.com
 */
namespace App\Controllers;

use YrPHP\Core\Controller;

class Index extends Controller
{
    function __construct()
    {
        parent::__construct();
    }
    function index()
    {
        echo "<h1>欢迎使用yrPHP 有什么建议或则问题 请随时联系我<br/>QQ：284843370<br/>email:kwinwong@hotmail.com</h1>";
    }
}