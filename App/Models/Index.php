<?php
/**
 * Created by PhpStorm.
 * User: TOYOTA
 * Date: 2016/10/31
 * Time: 13:48
 */

namespace App\Models;

use YrPHP\Core\Model;

class Index extends Model
{
    function __construct()
    {
        parent::__construct();
        $this->tableName = 'order';
    }

    function test1()
    {

    }

    function test()
    {
        return $this->select('shipping_name')->limit(1)->get()->row();
    }

    function getShippingNameAttribute($value)
    {
        return $value . '123';
    }

    function setStoreIdAttribute($value)
    {
        return $value + 100;
    }
}