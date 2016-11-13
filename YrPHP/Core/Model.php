<?php
/**
 * Created by YrPHP.
 * User: Kwin
 * QQ:284843370
 * Email:kwinwong@hotmail.com
 * GitHub:https://GitHubhub.com/quinnfox/yrphp
 */
namespace YrPHP\Core;

use Yrphp\Libs\Validate;

class Model
{
    // 当前数据库操作对象
    private static $object;
    //主服务器
    public $masterServer = null;
    //从服务器群
    public $slaveServer = array();
    //事务状态
    public $transStatus = true;
    //是否有事务正在执行
    protected $hasActiveTransaction = false;
    //是否验证 将验证字段在数据库中是否存在，不存在 则舍弃 再验证 $validate验证规则 不通过 则报错
    public $_validate = true;

    /**
     * 当前操作的数据库实例
     * @var \YrPHP\Core\Model
     */
    public $db = null;
    // 数据表前缀
    protected $tablePrefix = null;
    //数据库别称
    // protected $tableAlias = null;
    // 链操作方法列表
    protected $methods = array("field" => "", "where" => "", "order" => "", "limit" => "", "group" => "", "having" => "");
    //表名称
    protected $tableName = null;

    private $escapeTableName = null;
    //拼接后的sql语句
    protected $sql;
    //错误信息
    protected $error = array();
    //多表连接
    protected $join = array();
    //验证规则 array('字段名' => array(array('验证规则(值域)', '错误提示', '附加规则')));
    protected $validate = array();
    //是否开启缓存 bool
    protected $openCache;
    // query 预处理绑定的参数
    protected $parameters = array();
    //执行过的sql
    private $queries = array();

    public $connection = null;

    private $dbConfig = array();

    protected static $mutatorCache = [];

    protected static $preProcessCache = [];

    public function __construct()
    {

        if (defined('APP_MODE') && file_exists(APP_PATH . "Config/database_" . APP_MODE . ".php")) {
            $this->dbConfig = requireCache(APP_PATH . "Config/database_" . APP_MODE . ".php");
        } else {
            $this->dbConfig = requireCache(APP_PATH . "Config/database.php");
        }


        $this->openCache = C('openCache');

        if (is_null($this->connection))
            $this->connection = $this->dbConfig['defaultConnection'];

        $this->tablePrefix = $this->dbConfig[$this->connection]['masterServer']['tablePrefix'];

        //$this->validate = array('filed' => array(array('验证规则', '错误提示', '附加规则')));
    }


    public function connection($name)
    {
        $this->connection = $name;
    }

    public function getConnection()
    {
        return $this->connection;
    }

    public function getConnectionInstance()
    {
        $db = $this->dbConfig[$this->connection];


        $this->masterServer = self::getInstance($db['masterServer']);

        if (empty($db['slaveServer'])) {

            $this->slaveServer[] = $this->masterServer;

        } else {

            if (!is_array($db['slaveServer'])) $db['slaveServer'] = array($db['slaveServer']);

            foreach ($db['slaveServer'] as $v) {

                $this->slaveServer[] = self::getInstance($v);
            }
        }


    }

    /**
     * 返回当前终级类对象的实例
     * @param $db_config 数据库配置
     * @return object
     */
    public static function getInstance($dbConfig)
    {
        if (is_object(self::$object)) {
            return self::$object;
        } else {
            switch ($dbConfig['dbDriver']) {
                case 'mysqli' :
                    break;
                case 'access' :
                    break;
                default :
                    // self::$object = new pdo_driver($dbConfig);
                    self::$object = db\pdoDriver::getInstance($dbConfig);

            }

            if (self::$object instanceof db\IDBDriver) {
                return self::$object;
            } else {
                die('错误：必须实现db\Driver接口');
            }

        }
    }


    /**
     * 添加反引号``
     * @param $value
     * @param bool $type
     * @return string
     */
    protected function protect($value, $type = true)
    {
        if (!$type) {
            return $value;
        }
        $value = trim($value);
        $value = str_replace(array('`', "'", '"'), '', $value);
        // $as = explode(' as ', $value);
        $as = preg_split('/\s+(as|\s)\s+/', $value);
        $as = empty($as[1]) ? preg_split('/[\n\r\t\s]+/i', $value) : $as;
        if (!empty($as[1])) { //a.id as b
            $asLeft = trim($as[0]);
            $asRight = trim($as[1]);
            $dot = explode('.', $asLeft);
            if (!empty($dot[1])) {
                $value = "`$dot[0]`." . "`$dot[1]` as `$asRight`";
            }
            if (preg_match('/(count|sum|min|max|avg)\((.*)\)/Ui', $asLeft, $matches)) {
                $value = strtoupper($matches[1]) . "($matches[2]) as `$asRight`";
            } else {
                $value = "`$asLeft` as `$asRight`";
            }
            return $value;
        }
        if (preg_match('/(count|sum|min|max|avg)\((.*)\)/Ui', $value, $matches)) {
            return strtoupper($matches[1]) . "($matches[2])";
        }

        $dot = explode('.', $value);

        if (!empty($dot[1])) {
            $fields = "`$dot[0]`.";
            if ($dot[1] == '*') {
                $fields .= "$dot[1]";
            } else {
                $fields .= "`$dot[1]`";
            }
            return $fields;
        }

        return "`$value`";
    }


    /**
     * 设置缓存
     * @param array $config
     * @return $this
     */
    public function setCache($status = true)
    {
        $this->openCache = $status;

        return $this;
    }


    function setOptions($attributes = [])
    {
        foreach ($attributes as $k => $v) {
            $this->$k = $v;
        }
    }

    /**
     * 利用__call方法实现一些特殊的Model方法
     * @access public
     * @param string $method 方法名称
     * @param array $args 调用参数
     * @return $this
     */
    public function __call($method, $args)
    {
        if (empty($args[0])) return $this;
        $method = strtolower($method);
        if (in_array($method, array("order", "group"), true)) {
            // 连贯操作的实现
            $args = array_filter(explode(',', trim($args[0])));

            foreach ($args as $v) {
                if ($this->methods[$method] != "") $this->methods[$method] .= ',';
                $order = preg_split("/[\s,]+/", trim($v));

                $dot = explode('.', $order[0]);

                $this->methods[$method] .= '`' . $dot[0] . '`';

                if (isset($dot[1])) $this->methods[$method] .= ".`$dot[1]`";

                if (isset($order[1])) $this->methods[$method] .= ' ' . $order[1];

            }


        } else if ($method == "where") {

            $this->condition($args[0], isset($args[1]) ? $args[1] : "and");
        } else if ($method == "having") {

            $this->condition($args[0], isset($args[1]) ? $args[1] : "and", 'having');

        } else if (in_array($method, array('count', 'sum', 'min', 'max', 'avg'))) {

            $tableName = isset($args[0]) ? $args[0] : '';

            $field = isset($args[1]) ? $args[1] : '*';

            $auto = end($args) === false ? false : true;

            return $this->select($method . '(' . $field . ') as `c`')->get($tableName, $auto)->row()->c;

        }
        return $this;
    }


    /**
     * @param string $where id=1 || array('id'=>1)||array('id'=>array(1,'is|>|=|<>|like','null|or|and'))
     * @param string $where id between 20 and 100 || array('id'=>array('20 and 100','between|not between','null|or|and'))
     *
     * @param string $where id in 1,2,3,4,5,6,7,8 10 || array('id'=>array('1,2,3,4,5,6,7,8 10','in|not in','null|or|and'))
     * 也可以用数组
     * @param string $where id in 1,2,3,4,5,6,7,8 10 || array('id'=>array(array(1,2,3,4,5,6,7,8 10),'in|not in','null|or|and'))
     * @param string $logical and | or
     * @param string $type where | having
     * @return $this
     */
    protected function condition($where = '', $logical = "and", $type = "where")
    {
        if (empty($this->methods[$type])) {
            $this->methods[$type] = " $type ";
        } else {
            $this->methods[$type] .= " {$logical} ";
        }
        $this->methods[$type] .= '(';
        if (is_string($where)) {
            $this->methods[$type] .= $where;
        } elseif (is_array($where)) {
            $count = 0;
            foreach ($where as $k => $v) {
                $k = $this->protect($k);
                if (is_array($v)) {
                    $value = $v[0];
                    if (empty($v[1])) {
                        $symbol = "=";
                    } else {
                        $symbol = trim($v[1]);
                    }
                    $logical = empty($v[2]) ? $logical : $v[2];

                    if (strripos($symbol, 'is') !== false) {
                        $value = $value;
                    } elseif (strripos($symbol, 'in') !== false) {//in || not in

                        if (is_string($value)) {
                            $value = explode(',', $value);
                        }
                        $val = '';
                        foreach ($value as $vv) {
                            $val .= '"' . $vv . '",';
                        }

                        $value = '(' . trim($val, ',') . ')';

                    } elseif (strripos($symbol, 'between') !== false) {//between|not between
                        if (preg_match('/(.*)(and|or)(.*)/i', $value, $matches)) {
                            $value = " '" . trim($matches[1]) . "' " . trim($matches[2]) . " '" . trim($matches[3]) . "' ";
                        } else {

                            $value = " " . $value . " ";
                        }
                    } else {

                        $value = " '" . $value . "' ";
                    }

                    if ($count != 0) {
                        $this->methods[$type] .= " " . $logical . " ";
                    }

                    $this->methods[$type] .= " $k " . $symbol . $value;


                } else {

                    if ($count != 0) {
                        $this->methods[$type] .= " " . $logical . " ";
                    }

                    if (is_null($v)) {

                        $this->methods[$type] .= " $k is null";

                    } elseif (strripos($v, 'null') !== false) {

                        $this->methods[$type] .= " $k is {$v}";
                    } else {

                        $this->methods[$type] .= " $k " . "='$v'";
                    }
                }
                $count++;
            }
        }
        $this->methods[$type] .= ')';
        return $this;
    }

    /**
     * @param string $field
     * @param bool $safe 是否添加限定符：反引号，默认false不添加
     * @return $this
     */
    public final function select($field = '', $safe = false)
    {
        if (is_array($field)) {
            $fieldArr = $field;
        } else {
            $fieldArr = explode(',', $field);
        }
        foreach ($fieldArr as $k => $v) {
            if (!$safe || $v == '*') {
                $this->methods['field'] .= $v . ',';
            } else {
                $this->methods['field'] .= $this->protect($v) . ',';
            }
        }

        return $this;
    }

    /**
     * @param string $tableName
     * @param int $auto 1 自动添加前缀
     * @return $this
     */
    public final function table($tableName = "", $auto = 1)
    {

        if (empty($tableName)) {
            if (!is_null($this->escapeTableName)) return $this;

            $tableName = $this->tableName;
        }


        if ($auto)
            $tableName = strpos($tableName, $this->tablePrefix) === false ? $this->tablePrefix . $tableName : $tableName;


        $this->escapeTableName = strrpos($tableName, '`') === false ? $this->protect($tableName) : $tableName;

        return $this;
    }


    /**
     * @param string $tableName
     * @param bool $auto 是否自动添加表前缀
     * @return $this
     */
    public final function get($tableName = "", $auto = true)
    {

        $this->table($tableName, $auto);

        $tableName = $this->escapeTableName;


        //$tableField = $this->tableField();
        if (empty($this->methods['field'])) {
            //$field = implode(",", $tableField);
            $field = ' * ';
        } else {
            $field = trim($this->methods['field'], ',');
        }


        $order = $this->methods["order"] != "" ? " ORDER BY {$this->methods["order"]}" : "";
        $group = $this->methods["group"] != "" ? " GROUP BY {$this->methods["group"]}" : "";
        $having = $this->methods["having"] != "" ? "{$this->methods["having"]}" : "";

        $sql = "SELECT $field FROM  {$tableName}";

        if (is_array($this->join)) {
            foreach ($this->join as $v) {
                $sql .= " " . $v . " ";
            }
        }

        $sql .= "{$this->methods['where']}{$group}
                            {$having}{$order}{$this->methods['limit']}";
        $this->sql = $sql;


        return $this;
    }

    /**
     * 清除上次组合的SQL记录，避免重复组合
     */
    public final function cleanLastSql()
    {
        $this->join = "";
        $this->methods = array("field" => "", "where" => "", "order" => "", "limit" => "", "group" => "", "having" => "");
        $this->table($this->tableName);

    }

    /**
     * 指定查询数量
     * @access public
     * @param mixed $offset 起始位置
     * @param mixed $length 查询数量
     * @return $this
     */
    public final function limit($offset, $length = null)
    {
        if (is_null($length) && strpos($offset, ',')) {
            list($offset, $length) = explode(',', $offset);
        }
        $this->methods['limit'] = " LIMIT " . (int)$offset . ($length ? ',' . (int)$length : '');
        return $this;
    }

    /**
     * 指定分页
     * @access public
     * @param mixed $page 页数
     * @param mixed $listRows 每页数量
     * @return $this
     */
    public final function page($page, $listRows = null)
    {
        if (is_null($listRows) && strpos($page, ',')) {
            list($page, $listRows) = explode(',', $page);
        }
        $this->methods['limit'] = " LIMIT " . ((int)$page - 1) * (int)$listRows . ',' . (int)$listRows;
        return $this;
    }

    /**
     * @param $table 表名称
     * @param $cond  连接条件
     * @param string $type 连接类型
     * @param bool $auto 是否自动添加表前缀
     * @return $this
     */
    public final function join($table, $cond, $type = '', $auto = true)
    {

        $table = $auto ? $this->tablePrefix . $table : $table;
        $table = strrpos($table, '`') === false ? $this->protect($table) : $table;

        if ($type != '') {
            $type = strtoupper(trim($type));

            if (!in_array($type, array(
                'LEFT',
                'RIGHT',
                'OUTER',
                'INNER',
                'LEFT OUTER',
                'RIGHT OUTER'
            ))
            ) {
                $type = '';
            } else {
                $type .= ' ';
            }
        }


        // Strip apart the condition and protect the identifiers
        if (preg_match('/([\w\.]+)([\W\s]+)(.+)/', $cond, $match)) {
            $cond = $this->protect($match[1]) . $match[2] . $this->protect($match[3]);
        }

        // Assemble the JOIN statement
        $join = $type . 'JOIN ' . $table . ' ON ' . $cond;

        $this->join[] = $join;

        return $this;
    }

    /**
     * @param $data
     * @param $assoc
     * @return mixed
     */
    protected function getDataPreProcessFill($data, $assoc)
    {
        $preProcessCache = $this->getDataPreProcessAttr();

        if ($assoc == false) {
            foreach ($preProcessCache as $fieldName => $method) {
                foreach ($data as $v) {
                    if (!isset($v->{$fieldName})) break;

                    $v->{$fieldName} = $this->$method($v->{$fieldName});
                }
            }
        } else {
            foreach ($preProcessCache as $fieldName => $method) {
                foreach ($data as $v) {
                    if (!isset($v[$fieldName])) break;

                    $v[$fieldName] = $this->$method($v[$fieldName]);
                }
            }
        }


        return $data;
    }

    /**
     * @param $filed
     * @param $data
     * @return mixed
     */
    protected function setDataPreProcessFill($filed, $data)
    {
        $preProcessCache = $this->getDataPreProcessAttr('set');

        foreach ($preProcessCache as $fieldName => $method) {


            if (($key = array_search($fieldName, $filed)) === false) break;


            if (is_array($data[0])) {

                foreach ($data as $k => $v) {
                    $data[$k][$key] = $this->$method($data[$k][$key]);
                }

            } else {
                $data[$key] = $this->$method($data[$key]);
            }


        }


        return $data;
    }

    /**
     * @param string $type
     * @return array
     */
    protected function getDataPreProcessAttr($type = 'get')
    {
        if (!isset(static::$preProcessCache[$this->tableName][$type])) {
            $preProcessCache = $this->getMutatedAttributes();

            if (!isset($preProcessCache[$type])) return [];

            $getCache = $preProcessCache[$type];

            $fields = $this->tableField();

            $getAttr = [];
            foreach ($getCache as $v) {

                if ($key = arrayISearch($v, $fields)) {
                    $getAttr[$fields[$key]] = $type . $v . 'Attribute';
                } else if ($key = arrayISearch(parseNaming($v, 2), $fields)) {
                    $getAttr[$fields[$key]] = $type . $v . 'Attribute';;
                }
            }
            static::$preProcessCache[$this->tableName][$type] = $getAttr;
        }

        return static::$preProcessCache[$this->tableName][$type];
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
                    $mutatedAttributes['get'][] = $match;
                else
                    $mutatedAttributes['set'][] = $match;
            }
        }

        static::$mutatorCache[$class] = $mutatedAttributes;
    }


    /**
     * @param string $dbCacheFile 缓存文件
     * @param string $type 返回的数据类型 object|array
     * $openCache  bool|true 是否开启缓存
     * @return mixed 返回数据
     */
    protected final function cache($assoc = false, $row = 'result')
    {

        $type = $assoc ? 'arr' : 'obj';

        $dbCacheFile = $this->tableName . '_' . md5($this->sql . $type . $row);

        $cache = Cache::getInstance();

        $openCacheBak = $this->openCache;

        $this->openCache = C('openCache');

        if ($openCacheBak && !$cache->isExpired($dbCacheFile))
            return $cache->get($dbCacheFile)->data;


        $this->getConnectionInstance();
        $this->db = $this->slaveServer[array_rand($this->slaveServer, 1)];

        $query = $this->db->query($this->sql);

        if ($row == 'result') {
            $data = $query->result($assoc);
        } else {
            $data[] = $query->row($assoc);
        }


        Debug::stop();
        $debug = ['sql' => $this->sql, 'time' => Debug::spent()];

        $re = $this->getDataPreProcessFill($data, $assoc);

        if ($openCacheBak) $cache->set($dbCacheFile, (object)['sql' => $debug['sql'], 'data' => $re]);


        Debug::addMsg($debug, 2);
        return $re;

    }


    /**
     * @param bool|false $assoc 当该参数为 TRUE 时，将返回 array 而非 object 。
     * @return mixed
     */
    public final function row($assoc = false)
    {
        Debug::start();

        $re = $this->cache($assoc, 'row');


        return isset($re[0]) ? $re[0] : false;
    }


    /**
     * @param int $id
     * @param bool|false $assoc 当该参数为 TRUE 时，将返回 array 而非 object 。
     * @param string $tableName
     * @param bool $auto 是否自动添加表前缀
     * @return mixed
     */
    public function find($id = 0, $assoc = false, $tableName = "", $auto = true)
    {
        $field = $this->tableField();
        return $this->where([$field['pri'] => $id])->get($tableName, $auto)->row($assoc);
    }


    /**
     * @param bool $assoc 当该参数为 TRUE 时，将返回 array 而非 object 。
     * @param string $tableName
     * @param bool $auto 是否自动添加表前缀
     * @return mixed
     */
    public function all($assoc = false, $tableName = "", $auto = true)
    {
        return $this->get($tableName, $auto)->result($assoc);
    }

    /**
     * 返回数据集合
     * @param bool|false $assoc 当该参数为 TRUE 时，将返回 array 而非 object 。
     * @param bool|true $openCache 是否开启缓存
     * @return mixed
     */
    public final function result($assoc = false)
    {
        Debug::start();

        $re = $this->cache($assoc, 'result');

        return $re;
    }


    /**
     * @param string $tableName //数据库表名
     * @param string|array $where 条件
     * @param bool $auto 是否自动添加表前缀
     * @return int 返还受影响行数
     */
    public final function delete($where = "", $tableName = "", $auto = true)
    {

        $this->table($tableName, $auto);

        if (!empty($where)) $this->where($where);

        $where = $this->methods['where'];
        $limit = $this->methods['limit'];

        $this->sql = "DELETE FROM `{$this->escapeTableName}` {$where} {$limit}";


        $re = $this->query($this->sql)->result();
        if (!$re) {
            if ($this->hasActiveTransaction) $this->transStatus = false;

        }

        return $re;
    }

    /**
     * 添加单条数据
     * @param array $data 添加的数据
     * @param string $tableName 数据库表名
     * @param bool $auto 是否自动添加表前缀
     * @param string $act
     * @return int 受影响行数
     */
    public final function insert($data = array(), $tableName = "", $auto = true, $act = 'INSERT')
    {

        $this->table($tableName, $auto);

        if (empty($data)) $data = I('post');

        if (!$data) return false;

        $data = $this->check($data);

        if ($data === false) return false;

        $filed = array_keys($data);
        $data = array_values($data);
        return $this->inserts($filed, $data, $tableName, $auto, $act);

        /*        $field = $value = '';
                foreach ($data as $k => $v) {

                    $field .= "`$k`,";
                    $value .= "'$v',";
                }

                $field = trim($field, ',');
                $value = trim($value, ',');

                $this->sql = "{$act}  INTO " . $this->escapeTableName . "(" . $field . ")  VALUES(" . $value . ") ";


                $re = $this->query($this->sql);

                if (!$re->result())
                    if ($this->hasActiveTransaction) $this->transStatus = false;


        return $re->getLastId();
           */
    }

    /**
     * 添加单条数据 如已存在则替换
     * @param array $data 添加的数据
     * @param string $tableName 数据库表名
     * @param bool $auto 是否自动添加表前缀
     * @return int 受影响行数
     */
    function replace($data = array(), $tableName = "", $auto = true)
    {
        $this->insert($data, $tableName, $auto, $act = 'REPLACE');
    }


    /**
     * 预处理，添加多条数据
     * @param array $filed 字段
     * @param array $data 添加的数据
     * @param string $tableName 数据库表名
     * @param bool $auto 是否自动添加表前缀
     * @param string $act
     * @return int 受影响行数
     */
    function inserts($fields = array(), $data = array(), $tableName = "", $auto = true, $act = 'INSERT')
    {

        $data = $this->setDataPreProcessFill($fields, $data);

        $this->table($tableName, $auto);

        $field = implode(',', $fields);
        $value = trim(str_repeat('?,', count($fields)), ',');

        $this->sql = "{$act}  INTO " . $this->escapeTableName . "(" . $field . ")  VALUES(" . $value . ") ";


        $re = $this->query($this->sql, $data);

        if (!$re->result() && $this->hasActiveTransaction)
            $this->transStatus = false;


        return $re->getLastId();
    }

    /**
     * 预处理添加多条数据 如已存在则替换
     * @param array $filed 字段
     * @param array $data 添加的数据
     * @param string $tableName 数据库表名
     * @param bool $auto 是否自动添加表前缀
     * @return int 受影响行数
     */
    function replaces($filed = array(), $data = array(), $tableName = "", $auto = true)
    {
        $this->inserts($filed, $data, $tableName, $auto, $act = 'REPLACE');
    }


    /**
     * @param  array $array 要验证的字段数据
     * @param  string $tableName 数据表名
     * @param bool $auto
     * @return array
     *
     * array('字段名' => array(array('验证规则(值域)', '错误提示', '附加规则')));
     *附加规则:
     * equal:值域:string|null 当值与之相等时，通过验证
     * notequal:值域:string|null 当值与之不相等时 通过验证
     * in:值域:array(1,2,3)|1,2,3 当值存在指定范围时 通过验证
     * notin: 值域:array(1,2,3)|1,2,3  当不存在指定范围时 通过验证
     * between: 值域:array(1,30)|1,30 当存在指定范围时 通过验证
     * notbetween:值域:array(1,30)|1,30 当不存在指定范围时 通过验证
     * length:值域:array(10,30)|10,30 当字符长度大于等于10，小于等于30时 通过验证 || array(30)|30 当字符等于30时 通过验证
     * unique:值域:string 当该字段在数据库中不存在该值时 通过验证
     * email： 值域：string 当值为email格式时 通过验证
     * url： 值域：string 当值为url格式时 通过验证
     * number: 值域：string 当值为数字格式时 通过验证
     * regex:值域:正则表达式 //当符合正则表达式时 通过验证
     *
     */

    public final function check($array, $tableName = "", $auto = true)
    {

        if ($this->_validate) {
            $this->table($tableName, $auto);

            $tableField = $this->tableField();

            foreach ($array as $key => &$value) {

                if (!in_array(strtolower($key), array_map('strtolower', $tableField))) {//判断字段是否存在 不存在则舍弃
                    unset($array[$key]);
                } else {
                    /*** 验证规则validate*****/
                    if (isset($this->validate[$key])) {//判断验证规则是否存在

                        foreach ($this->validate[$key] as $validate) {

                            if (empty($validate[1]))
                                $validate[1] = "错误:{$key}验证不通过";


                            if (method_exists('\YrPHP\Libs\Validate', $validate[2])) {

                                if (!Validate::$validate[2]($value, $validate[0]))
                                    $this->error[$key] = $validate[1];

                            }
                        }
                    }


                }


            }

            if (!empty($this->error)) {
                session('errors', $this->error);
                return false;
            }
        }

        if (!get_magic_quotes_gpc()) {
            $array = array_map('addslashes', $array);//回调过滤数据($data);
        }

        return $array;
    }


    /**
     * 自动获取表结构
     * @param string $tableName 数据库表名
     * @param bool $auto 是否自动添加表前缀
     * @return array|bool
     */
    public final function tableField($tableName = "", $auto = true)
    {

        $result = $this->checkTable($tableName, $auto);

        foreach ($result as $k => $row) {
            // $row["Field"] = strtolower($row["Field"]);
            if ($row->Key == "PRI") {
                $fields["pri"] = $row->Field;
            } else {
                $fields[] = $row->Field;
            }

            // if ($row->Extra == "auto_increment")    $fields["auto"] = $row["Field"];

        }
        //如果表中没有主键，则将第一列当作主键
        if (isset($fields)) {
            if (!array_key_exists("pri", $fields)) {
                $fields["pri"] = array_shift($fields);
            }
            return $fields;
        }
        return false;
    }

    /**
     * 返回受上一个 SQL 语句影响的行数
     * @return int
     */
    public final function rowCount()
    {
        if ($this->db === null) return 0;
        return $this->db->rowCount();

    }

    /**
     * @param string $sql
     * @param array $parameters array|''
     * @return $this|\PDOStatement
     */
    public final function query($sql = "", $parameters = array())
    {
        Debug::start();
        if (!empty($sql)) $this->sql = $sql;

        $this->queries[] = $this->sql;
        $this->cleanLastSql();

        $this->parameters = !is_array($parameters) ? array() : $parameters;

        $this->getConnectionInstance();

        if (stripos($sql, 'select') === false) {
            $this->queries[] = $sql;
            $this->db = $this->masterServer;
            $re = $this->db->query($this->sql, $parameters);
            Debug::stop();
            Debug::addMsg(array('sql' => $sql, 'time' => Debug::spent()), 2);
            return $re;
        } else {
            return $this;
        }

    }


    /**
     * @param array $data 更改的数据
     * @param string $tableName 数据库表名
     * @param string|array $where 更改条件
     * @param bool $auto 是否自动添加表前缀
     * @return int 返回受影响行数
     */
    public final function update($data = array(), $where = "", $tableName = "", $auto = true)
    {
        $this->table($tableName, $auto);

        if (empty($data))
            $data = I('post');


        if (!$data)
            return false;

        $data = $this->check($data);

        if ($data === false) return false;

        if (!empty($where))
            $this->where($where);

        $where = $this->methods['where'];
        $limit = $this->methods['limit'];

        $NData = '';

        foreach ($data as $k => $v) {
            $NData .= '`' . $k . "`='" . $v . "',";
        }

        $NData = trim($NData, ',');


        $this->sql = "UPDATE `" . $this->escapeTableName . "` SET " . $NData . " " . $where . " " . $limit . "";


        $re = $this->query($this->sql)->result();

        if (!$re) {
            if ($this->hasActiveTransaction) $this->transStatus = false;

        }

        return $re;
    }


    /**
     * 所有sql语句
     * @return array
     */
    public final function history()
    {
        return $this->queries;
    }


    /**
     * 最后一条sql语句
     * @return mixed
     */
    public final function lastQuery()
    {
        return end($this->queries);
    }

    /**
     * 最后一条sql语句
     * @return mixed
     */
    public final function lastSql()
    {

        return end($this->queries);
    }

    /**
     * SQL历史记录
     * @return array
     */
    public final function historySql()
    {
        return $this->queries;
    }

    /**
     * 启动事务处理模式
     * @return bool 成功返回true，失败返回false
     */
    public final function startTrans()
    {
        if ($this->hasActiveTransaction)
            return false;

        $this->hasActiveTransaction = $this->masterServer->beginTransaction();
        return $this->hasActiveTransaction;

    }

    /**
     * 启动事务处理模式
     * @return bool 成功返回true，失败返回false
     */
    public final function transComplete()
    {
        if ($this->transStatus === FALSE)
            $this->rollback();
        else
            $this->commit();


        $this->transStatus = true;
        return $this->transStatus;
    }


    /**
     * 事务回滚
     * @return bool 成功返回true，失败返回false
     */
    public final function rollback()
    {
        $this->transStatus = true;
        $this->hasActiveTransaction = false;

        return $this->masterServer->rollBack();
    }

    /*
     * @return 错误信息
     */

    /**
     * 提交事务
     * @return bool 成功返回true，失败返回false
     */
    public final function commit()
    {
        $this->transStatus = true;
        $this->hasActiveTransaction = false;

        return $this->masterServer->commit();
    }

    /**
     * 获取最后一次插入的自增值
     * @return bool|int 成功返回最后一次插入的id，失败返回false
     */
    public final function getLastId()
    {
        return $this->masterServer->getLastId();
    }


    /**
     *获得验证错误提示
     * @return mixed
     */
    public final function getError()
    {
        return $this->error;
    }

    public function __destruct()
    {
        $this->cleanLastSql();
        $this->error = array();
    }


    /*--------------------------数据库操作功能---------------------------------*/

    /**
     * 创建数据库，并且主键是id
     * @param string $tableName 表名
     * @param string $key 主键
     * @param string $engine 引擎 默认InnoDB
     * @param bool $auto 是否自动添加表前缀
     */
    function createTable($tableName = '', $key = 'id', $engine = 'InnoDB', $auto = true)
    {

        $this->table($tableName, $auto);

        $sql = "CREATE TABLE IF NOT EXISTS {$this->escapeTableName} (`$key` INT NOT NULL AUTO_INCREMENT  primary key) ENGINE = {$engine};";
        $this->query($sql);

    }


    /**
     * 删除表
     * @param string $tableName 数据库表名
     * @param bool $auto 是否自动添加表前缀
     * @return mixed
     */
    function dropTable($tableName = '', $auto = true)
    {

        $this->table($tableName, $auto);

        $sql = " DROP TABLE IF EXISTS $this->escapeTableName";
        return $this->query($sql)->result;
    }


    /**
     * 检测表是否存在，也可以获取表中所有字段的信息(表里所有字段的信息)
     * @param string $tableName 要查询的表名
     * @param bool $auto 是否自动添加表前缀
     * @return mixed
     */
    function checkTable($tableName = '', $auto = true)
    {
        $this->table($tableName, $auto);
        $cache = Cache::getInstance();
        $sql = "desc $this->escapeTableName";
        $dbCacheFile = md5($sql);

        if (!$cache->isExpired($dbCacheFile))
            return $cache->get($dbCacheFile);


        $info = $this->query($sql)->result();

        $cache->set($dbCacheFile, $info);

        return $info;
    }


    /**
     * 检测字段是否存在，也可以获取字段信息(只能是一个字段)
     * @param string $field 字段名
     * @param string $tableName 表名
     * @param bool $auto 是否自动添加表前缀
     * @return mixed
     */
    function checkField($field = '', $tableName = '', $auto = true)
    {

        $this->table($tableName, $auto);

        $sql = "desc {$this->escapeTableName} $field";
        $info = $this->query($sql)->row();
        return $info;
    }


    /**
     * @param array $info 字段信息数组
     * @param string $tableName 表名
     * @param bool $auto 是否自动添加表前缀
     * @return array 字段信息
     */
    function addField($info = array(), $tableName = '', $auto = true)
    {
        $this->table($tableName, $auto);


        $sql = "alter table {$this->escapeTableName} add ";
        $sql .= $this->filterFieldInfo($info);

        return $this->query($sql)->result;
    }


    /**
     * 修改字段
     * 不能修改字段名称，只能修改
     * @param array $info
     * @param string $tableName
     * @param bool $auto 是否自动添加表前缀
     * @return mixed
     */
    function editField($info = array(), $tableName = '', $auto = true)
    {

        $this->table($tableName, $auto);

        $sql = "alter table {$this->escapeTableName} modify ";
        $sql .= $this->filterFieldInfo($info);

        return $this->query($sql)->result;

        $this->checkField($info['name']);
    }

    /*
     * 字段信息数组处理，供添加更新字段时候使用
     * info[name]   字段名称
     * info[type]   字段类型
     * info[length]  字段长度
     * info[isNull]  是否为空
     * info['default']   字段默认值
     * info['comment']   字段备注
     */
    private function filterFieldInfo($info = array())
    {
        if (!is_array($info)) return false;

        $newInfo = array();
        $newInfo['name'] = $info['name'];
        $newInfo['type'] = strtolower($info['type']);
        switch ($info['type']) {
            case 'varchar':
            case 'char':
                $newInfo['length'] = isset($info['length']) ? 100 : $info['length'];
                $newInfo['default'] = isset($info['default']) ? 'DEFAULT "' . $info['default'] . '"' : '';

                break;
            case 'int':
                $newInfo['length'] = isset($info['length']) ? 7 : $info['length'];
                $newInfo['default'] = isset($info['default']) ? 'DEFAULT ' . (int)$info['default'] : 0;

                break;
            case 'text':
                $newInfo['length'] = '';
                $newInfo['default'] = '';
                break;
        }
        $newInfo['isNull'] = !empty($info['isNull']) ? ' NULL ' : ' NOT NULL ';
        $newInfo['comment'] = isset($info['comment']) ? ' ' : ' COMMENT "' . $info['comment'] . '" ';

        $sql = $newInfo['name'] . ' ' . $newInfo['type'];
        $sql .= (!empty($newInfo['length'])) ? '(' . $newInfo['length'] . ')' . " " : ' ';
        $sql .= $newInfo['isNull'];
        $sql .= $newInfo['default'];
        $sql .= $newInfo['comment'];
        return $sql;
    }


    /**
     * 删除字段
     * 如果返回了字段信息则说明删除失败，返回false，则为删除成功
     * @param string $field
     * @param string $tableName
     * @param bool $auto
     * @return mixed
     */
    function dropField($field = '', $tableName = '', $auto = true)
    {

        $this->table($tableName, $auto);

        $sql = "alter table {$this->escapeTableName} drop column $field";
        return $this->query($sql)->result;

    }


    /**
     * 获取指定表中指定字段的信息(多字段)
     * @param array $field
     * @param string $tableName
     * @param bool $auto
     * @return array
     */
    function getFieldInfo($field = array(), $tableName = '', $auto = true)
    {

        $this->table($tableName, $auto);

        $info = array();
        if (is_string($field)) {
            $field = explode(',', $field);
        }

        foreach ($field as $v) {
            $info[$v] = $this->checkField($v);
        }

        return $info;
    }


}