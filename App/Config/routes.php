<?php
/**
 * Created by YrPHP.
 * User: Kwin
 * QQ:284843370
 * Email:kwinwong@hotmail.com
 * GitHub:https://github.com/kwinH/YrPHP
 */

//Route::get('/',  'Test\Index@index');

//    Route::controller('user', 'UserController');
Route::group([
    'namespace' => 'App\\Controllers',
    // 'view'=>'test'
], function ($route) {
    $route->get('/',function(){
       return 'ddd';
    });
    $route->controller('user', 'UserController');




    Route::group([
        'namespace' => '\\Test'
    ], function ($route) {
        $route->controller('test', 'Index');
    });

});


//Route::group([
//   'namespace' =>'App\\Controllers\\Test\\'
//],function($route){
//    $route->controller('test', 'Index');
//});

