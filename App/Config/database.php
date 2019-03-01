<?php
/**
 * Created by YrPHP.
 * User: Kwin
 * QQ:284843370
 * Email:kwinwong@hotmail.com
 */

//数据库配置例子 请将该文件复制到你的项目下的config文件夹下 不允许直接在该文件下配置
return [

    'defaultConnection' => 'default',

    'default' => [

//        'master' => [],//主服务器
//        'slave' => [],//从服务器
        'driver' => 'pdo', // 数据库类型
        'type' => 'mysql', // 数据库类型
        'host' => '10.0.11.230', // 服务器地址
        'dbname' => 'swoole', // 数据库名
        'user' => 'root', // 用户名
        'password' => 'root', // 密码
        'port' => '3306', // 端口
        'prefix' => '', // 数据库表前缀
        'charset' => 'utf8',
    ]
];
