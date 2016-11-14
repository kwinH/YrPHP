<?php
/**
 * Created by YrPHP.
 * User: Kwin
 * QQ:284843370
 * Email:kwinwong@hotmail.com
 */
namespace App\Controllers;

use YrPHP\Core\Controller;
use YrPHP\Libs\Validate;

class Index extends Controller
{
    protected static $mutatorCache = [];
    protected static $preProcessCache = [];

    function __construct()
    {
        parent::__construct();
        $this->tableName = 'order';
    }


    function index()
    {

        $db = M('Index');
        $t = $db->test();
var_dump($db->getMutatedAttributes());

  //  $db->insert(array('order_code'=>'12345678dfgd','store_id'=>'80'));
        DIE;

        //  var_export($list);
        // $c = Model('test\index')->test();
        //  var_export($c);
        //  echo "<h1>欢迎使用yrPHP 有什么建议或则问题 请随时联系我<br/>QQ：284843370<br/>email:kwinwong@hotmail.com</h1>";


        //  $this->display('index.html', ['a' => 'dfd']);
    }

    public function dataPreProcessFill($data)
    {
        $preProcessCache = $this->getDataPreProcessAttr();

        foreach ($preProcessCache as $fieldName => $method) {
            foreach ($data as $v) {
                if (!isset($v->$fieldName)) break;

                $v->$fieldName = $method($v->$fieldName);
            }
        }

        return $data;
    }

    public function getDataPreProcessAttr()
    {
        if (!isset(static::$preProcessCache[$this->tableName]['get'])) {
            $preProcessCache = $this->getMutatedAttributes();

            if (!isset($preProcessCache['get'])) return [];

            $getCache = $preProcessCache['get'];

            $fields = ['id', 'name', 'num', 'Car_Owner'];

            $getAttr = [];
            foreach ($getCache as $v) {
                if ($key = arrayISearch(parseNaming($v, 2), $fields)) {
                    $getAttr[$fields[$key]] = $v;
                } else if ($key = arrayISearch($v, $fields)) {
                    $getAttr[$fields[$key]] = $v;
                }
            }
            static::$preProcessCache[$this->tableName]['get'] = $getAttr;
        }

        return static::$preProcessCache[$this->tableName]['get'];
    }


    public function getMutatedAttributes()
    {
        $class = get_class($this);

        if (!isset(static::$mutatorCache[$class])) {
            static::cacheMutatedAttributes($class);
        }

        return static::$mutatorCache[$class];
    }

    public static function cacheMutatedAttributes($class)
    {
        $mutatedAttributes = [];

        // Here we will extract all of the mutated attributes so that we can quickly
        // spin through them after we export models to their array form, which we
        // need to be fast. This'll let us know the attributes that can mutate.


        if (preg_match_all('/(?<=^|;)(get|set)([^;]+?)Attribute(;|$)/', implode(';', get_class_methods($class)), $matches)) {

            foreach ($matches[2] as $key => $match) {

                //   $match = parseNaming($match, 2);

                if ($matches[1][$key] == 'get')
                    $mutatedAttributes['get'][] = lcfirst($match);
                else
                    $mutatedAttributes['set'][] = lcfirst($match);
            }
        }

        static::$mutatorCache[$class] = $mutatedAttributes;
    }


    function getCarOwnerAttribute()
    {
        echo '35';
    }

    function setCarOwnerAttribute()
    {
        echo '35';
    }
}