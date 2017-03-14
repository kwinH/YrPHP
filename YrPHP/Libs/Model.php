<?php
/**
 * Created by YrPHP.
 * User: Kwin
 * QQ:284843370
 * Email:kwinwong@hotmail.com
 * GitHub:https://github.com/kwinH/YrPHP
 */
namespace YrPHP;

class Model
{
    // 当前数据库操作对象
    private static $object;
    /**
     * 主服务器
     * @var \YrPHP\Db\PdoDriver
     */
    public $masterServer = null;
    /**
     * 从服务器群
     * @var \YrPHP\Db\PdoDriver
     */
    protected $slaveServer = [];

    //是否有事务正在执行
    protected $hasActiveTransaction = false;
    //事务状态
    public $transStatus = false;

    //是否验证 将验证字段在数据库中是否存在，不存在 则舍弃 再验证 $validate验证规则 不通过 则报错
    public $_validate = true;

    /**
     * 当前操作的数据库实例
     * @var \YrPHP\Db\PdoDriver
     */
    public $db = null;
    // 数据表前缀
    protected $tablePrefix = null;

    // 链操作方法列表
    protected $methods = [
        'field' => '',
        'where' => '',
        'order' => '',
        'limit' => '',
        'group' => '',
        'having' => '',
        'join' => [],
        'on' => '',
    ];

    //表名称
    protected $tableName = null;

    /**
     * 转义后的表名
     * @var null
     */
    private $escapeTableName = null;

    //拼接后的sql语句
    protected $sql;
    //错误信息
    protected $error = [];

    //是否开启缓存 bool
    protected $openCache;
    // query 预处理绑定的参数
    protected $parameters = [];
    //执行过的sql
    private $queries = [];

    public $connection = null;

    private $dbConfig = [];

    protected static $mutatorCache = [];

    protected static $preProcessCache = [];

    private $PreProcessStatus = true;

    private static $tableFileds = [];

    public function __construct($tableName = '')
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

        if ($tableName)
            $this->tableName = $tableName;

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
        $startTime = time();
        if (is_object(self::$object)) {
            $obj = self::$object;
        } else {
            switch ($dbConfig['dbDriver']) {
                case 'mysqli' :
                    break;
                case 'access' :
                    break;
                default :
                    // self::$object = new pdo_driver($dbConfig);
                    self::$object = Db\PdoDriver::getInstance($dbConfig);

            }

            if (self::$object instanceof Db\IDBDriver) {
                $obj = self::$object;
            } else {
                die('错误：必须实现db\Driver接口');
            }

        }
        $endTime = time();
        Debug::addMsg(['sql' => '数据库连接时间', 'time' => Debug::spent($startTime, $endTime)], 2);
        return $obj;
    }


    public function escapeId($field = '')
    {
        if (empty($field)) return '';

        if (is_array($field)) {
            return trim(array_reduce($field, function ($result, $item) {
                return $result . ',' . $this->escapeId($item);
            }), ',');
        } else {
            $field = explode('.', $field);
            $field[0] = "`{$field[0]}`";

            if (isset($field[1])) $field[0] .= ".`{$field[1]}`";

            return $field[0];
        }
    }

    public function escape($value = '')
    {
        if (is_array($value)) {
            if (isAssoc($value)) {
                $val = '';
                foreach ($value as $k => $v) {
                    $val .= $this->escapeId($k) . '=' . $this->escape($v);
                }
                return $val;
            } else {
                return '(' . array_reduce($value, function ($result, $item) {
                    return $result . ($result ? ',' : '') . $this->escape($item);
                }) . ')';
            }

        } else if (is_string($value)) {
            return '"' . trim($value) . '"';
        } else if (is_numeric($value)) {
            return $value;
        }

        return false;
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

            return $this->table($tableName, $auto)->select($method . '(' . $field . ') as c')->get()->row()->c;

        }
        return $this;
    }


    /**
     * @param string $where id=1 || ['id'=>1,'or id'=>2,'age >'=>15,'or id in'=>[1,2,3,4,5]]
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
                $filed = preg_split('/\s+/', $k);

                if (count($filed) == 3) {
                    $logical = filed[0];
                    $operator = filed[1];
                    $filed = $this->escapeId(filed[2]);
                } else {
                    if (preg_match('/and|or/i', $filed[0]) !== 0) {
                        $logical = $filed[0];
                        $operator = isset($filed[2]) ? $filed[2] : '=';
                        $filed = $this->escapeId($filed[1]);
                    } else {
                        $operator = isset($filed[1]) ? $filed[1] : '=';
                        $filed = $this->escapeId($filed[0]);
                    }
                }


                if ($count != 0) {
                    $this->methods[$type] .= ' ' . $logical . ' ';
                }

                if ($v instanceof \Closure) {
                    $this->methods[$type] .= $filed . ' ' . $operator . ' (' . call_user_func($v, new Model($this->tableName)) . ')';
                } else if (is_null($v)) {
                    $this->methods[$type] .= $filed . ' is null';
                } elseif (strripos($v, 'null') !== false) {
                    $this->methods[$type] .= $filed . "  is {$v}";
                } else {
                    $operator = strtoupper($operator);
                    if (strpos($operator, 'IN') !== false) {
                        if (is_string($v)) $v = explode(',', $v);
                        $val = $this->escape($v);
                    } else if (strpos($operator, 'BETWEEN') !== false) {
                        if (is_string($v)) $v = explode(',', $v);
                        $val = $this->escape($v[0]) . ' and  ' . $this->escape($v[1]);
                    } else {
                        if (strpos($type, 'on') !== false) {
                            if (is_string($v)) $v = explode(',', $v);
                            $val = $this->escapeId($v);
                        } else {
                            $val = $this->escape($v);
                        }

                    }
                    $this->methods[$type] .= $filed . ' ' . $operator . ' ' . $val;
                }
                $count++;

            }
        }
        $this->methods[$type] .= ')';
        return $this;

    }


    /**
     * @param array $field
     * @return $this
     */
    public final function select($field = [])
    {
        $this->field($field);
        return $this;
    }

    /**
     * @param array $field
     * @return $this
     */
    public final function field($field = [])
    {
        if (is_array($field)) {
            $fieldArr = $field;
        } else {
            $fieldArr = explode(',', $field);
        }

        $field = $this->escapeId($fieldArr);
        //count(*) as c
        $field = preg_replace('/`(.*)\s*\((.*)\)\s*(as|\s+)\s*(\S*)`/isU', '$1(`$2`) $3 `$4`', $field);
        $field = preg_replace('/`\*`/', "*", $field);
        $this->methods['field'] .= ',' . $field;
        $this->methods['field'] = trim($this->methods['field'], ',');

        return $this;
    }

    /**
     * @param array $field
     * @param string $tableName
     * @param bool $auto
     * @return $this
     */
    public final function except($field = [])
    {
        $tableField = $this->tableField();

        $field = array_diff($tableField, $field);

        $this->methods['field'] .= implode(',', $field);

        return $this;
    }

    /**
     * @param string $tableName
     * @param int $auto 1 自动添加前缀
     * @return $this
     */
    public final function table($tableName = "", $auto = true)
    {
        $this->setEscapeTableName($tableName, $auto);
        return $this;
    }

    protected final function setEscapeTableName($tableName = "", $auto = true)
    {
        if (empty($tableName)) {
            $tableName = $this->tableName;
        } else if ($tableName instanceof \Closure) {
            return $this->escapeTableName = ' (' . call_user_func($tableName, new Model($this->tableName)) . ') as tmp' . uniqid();
        }

        if ($auto && !empty($this->tablePrefix))
            $tableName = strpos($tableName, $this->tablePrefix) === false
                ? $this->tablePrefix . $tableName
                : $tableName;

        return $this->escapeTableName = $this->escapeId($tableName);
    }

    protected final function getEscapeTableName()
    {
        if (is_null($this->escapeTableName))
            $this->setEscapeTableName($this->tableName);

        return $this->escapeTableName;
    }

    /**
     * 获得表名
     * @return null|string
     */
    public function getTable()
    {
        return $this->tableName;
    }

    public function buildSql()
    {
        $this->getEscapeTableName();

        $field = $this->methods['field'] ? $this->methods['field'] : '*';
        $order = $this->methods["order"] != "" ? " ORDER BY {$this->methods["order"]} " : "";
        $group = $this->methods["group"] != "" ? " GROUP BY {$this->methods["group"]}" : "";
        $having = $this->methods["having"] != "" ? "{$this->methods["having"]}" : "";

        $this->sql = "SELECT $field FROM  {$this->escapeTableName}";

        if (is_array($this->methods['join'])) {
            foreach ($this->methods['join'] as $v) {
                $this->sql .= " " . $v . " ";
            }
        }

        $this->sql .= "{$this->methods['where']}{$group}{$having}{$order}{$this->methods['limit']}";
        $this->cleanLastSql();
        return $this->sql;
    }


    /**
     * @return $this
     */
    public final function get()
    {
        $this->buildSql();
        return $this;
    }


    /**
     * 以主键为条件 查询
     * @param int $id 查询的条件主键值
     * @param bool|false $assoc 当该参数为 TRUE 时，将返回 array 而非 object 。
     * @return mixed
     */
    public function find($id = 0, $assoc = false)
    {
        $field = $this->tableField();
        return $this->where([$field['pri'] => $id])->get()->row($assoc);
    }


    /**
     * @param bool $assoc 当该参数为 TRUE 时，将返回 array 而非 object 。
     * @return mixed
     */
    public function all($assoc = false)
    {
        return $this->get()->result($assoc);
    }


    /**
     * 清除上次组合的SQL记录，避免重复组合
     */
    public final function cleanLastSql()
    {
        $this->methods = [
            'field' => '',
            'where' => '',
            'order' => '',
            'limit' => '',
            'group' => '',
            'having' => '',
            'join' => [],
            'on' => '',
        ];
        $this->escapeTableName = null;
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
    public final function join($table = '', $cond = [], $type = '', $auto = true)
    {
        $table = $auto ? $this->tablePrefix . $table : $table;
        $table = preg_split('/\s+as\s+|\s+/', $table);
        $tableAlias = isset($table[1]) ? $this->escapeId($table[1]) : '';
        $table = $this->escapeId($table[0]);

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
        $this->methods['on'] = '';
        $this->condition($cond, 'and', 'on');

        $join = $type . 'JOIN ' . $table . ' ' . $tableAlias . ' ON ' . $this->methods['on'];
        $this->methods['join'] = $join;


        return $this;
    }

    /**
     * 临时关闭预处理功能
     * @return $this
     */
    public function closePreProcess()
    {
        $this->PreProcessStatus = false;
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

        if (empty($preProcessCache)) {
            return false;
        } else {
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

                if ($key = Arr::arrayISearch($v, $fields)) {
                    $getAttr[$fields[$key]] = $type . $v . 'Attribute';
                } else if ($key = Arr::arrayISearch(parseNaming($v, 2), $fields)) {
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
        $dbCacheFile = $this->tableName . '_' . md5($this->sql) . '_' . $row . (int)$assoc . (int)$this->PreProcessStatus;

        $cache = Cache::getInstance();

        $openCacheBak = $this->openCache;

        $this->openCache = C('openCache');

        if ($openCacheBak && !$cache->isExpired($dbCacheFile))
            return $cache->get($dbCacheFile)->data;


        $this->getConnectionInstance();
        $this->db = $this->slaveServer[array_rand($this->slaveServer, 1)];

        $query = $this->db->query($this->sql);
        $this->queries[] = $this->sql;

        if ($row == 'result') {
            $data = $query->result($assoc);
        } else {
            $data[] = $query->row($assoc);
        }

        if ($this->PreProcessStatus == true) {
            $re = $this->getDataPreProcessFill($data, $assoc);
        } else {
            $this->PreProcessStatus = true;
        }


        if ($openCacheBak) $cache->set($dbCacheFile, (object)['sql' => $this->sql, 'data' => $re]);

        return $re;

    }


    /**
     * @param bool|false $assoc 当该参数为 TRUE 时，将返回 array 而非 object 。
     * @return mixed
     */
    public final function row($assoc = false)
    {
        $re = $this->cache($assoc, 'row');


        return isset($re[0]) ? $re[0] : false;
    }


    /**
     * 返回数据集合
     * @param bool|false $assoc 当该参数为 TRUE 时，将返回 array 而非 object 。
     * @return mixed
     */
    public final function result($assoc = false)
    {
        $re = $this->cache($assoc, 'result');

        return $re;
    }


    /**
     * @param string $tableName //数据库表名
     * @param string|array $where 条件
     * @return int 返还受影响行数
     */
    public final function delete($where = "")
    {
        $this->getEscapeTableName();
        if (!empty($where)) $this->where($where);

        $where = $this->methods['where'];
        $limit = $this->methods['limit'];

        $this->sql = "DELETE FROM {$this->escapeTableName} {$where} {$limit}";

        $re = $this->query($this->sql)->result();

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
    public final function insert($data = [], $act = 'INSERT')
    {
        if (empty($data)) $data = \request::post();

        if (!$data) return false;

        $data = $this->check($data);

        if ($data === false) return false;


        return $this->inserts($data, $act);
    }

    /**
     * 添加单条数据 如已存在则替换
     * @param array $data 添加的数据
     * @param string $tableName 数据库表名
     * @param bool $auto 是否自动添加表前缀
     * @return int 受影响行数
     */
    function replace($data = [], $tableName = "", $auto = true)
    {
        $this->insert($data, $tableName, $auto, $act = 'REPLACE');
    }


    /**
     * 预处理，添加多条数据
     * @param array $data 添加的数据 单条：[filed=>val]| 多条：[[filed=>val],[filed=>val]]
     * @param string $act
     * @return int 受影响行数
     */
    function inserts($data = [], $act = 'INSERT')
    {
        $this->getEscapeTableName();
        if (is_array($data[0])) {
            $fields = array_keys($data[0]);
        } else {
            $fields = array_keys($data);
        }

        if ($this->PreProcessStatus == true) {
            $values = $this->setDataPreProcessFill($fields, $data);
            $data = $values === false ? $data : $values;
        } else {
            $this->PreProcessStatus = true;
        }


        $field = $this->escapeId($fields);
        //$value = trim(str_repeat('?,', count($fields)), ',');

        $value = trim(array_reduce($fields, function ($res, $item) {
            return $res .= ',:' . $item;
        }), ',');

        $this->sql = "{$act}  INTO " . $this->escapeTableName . "(" . $field . ")  VALUES(" . $value . ") ";


        $re = $this->query($this->sql, $data);


        return $re->getLastId();
    }

    /**
     * 预处理添加多条数据 如已存在则替换
     * @param array $filed 字段
     * @param array $data 添加的数据
     * @return int 受影响行数
     */
    function replaces($filed = [], $data = [])
    {
        $this->inserts($filed, $data, $act = 'REPLACE');
    }


    /**
     * @param  array $array 要验证的字段数据
     * @param  string $tableName 数据表名
     * @param bool $auto
     * @return array
     *
     */

    public final function check($array)
    {
        if ($this->_validate) {

            $tableField = $this->tableField();

            foreach ($array as $key => &$value) {

                if (!in_array(strtolower($key), array_map('strtolower', $tableField))) {//判断字段是否存在 不存在则舍弃
                    unset($array[$key]);
                }


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
    public final function tableField()
    {
        if (isset(self::$tableFileds[$this->escapeTableName]))
            return self::$tableFileds[$this->escapeTableName];


        $sql = 'desc ' . $this->setEscapeTableName();

        $result = $this->query($sql)->result();


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
            self::$tableFileds[$this->tableName] = $fields;

            return self::$tableFileds[$this->tableName];
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
    public final function query($sql = "", $parameters = [])
    {
        if (!empty($sql)) $this->sql = $sql;

        $this->queries[] = $this->sql;


        $this->parameters = !is_array($parameters) ? [] : $parameters;

        $this->getConnectionInstance();

        $this->cleanLastSql();
        if (stripos($sql, 'select') === false) {
            $this->queries[] = $sql;
            $this->db = $this->masterServer;

            $re = $this->db->query($this->sql, $parameters);

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
    public final function update($data = [], $where = "")
    {
        $this->getEscapeTableName();

        if (empty($data))
            $data = \request::post();

        if (!$data)
            return false;

        $data = $this->check($data);

        if ($data === false) return false;

        if (!empty($where))
            $this->where($where);

        $where = $this->methods['where'];
        $limit = $this->methods['limit'];

        if ($this->PreProcessStatus == true) {
            $fields = array_keys($data);
            $values = array_values($data);
            $values = $this->setDataPreProcessFill($fields, $values);
            $data = $values === false ? $data : $values;
        } else {
            $this->PreProcessStatus = true;
        }

        $data = $this->escape($data);

        $this->sql = "UPDATE " . $this->escapeTableName . " SET " . $data . " " . $where . " " . $limit;


        $re = $this->query($this->sql)->result();


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
        if ($this->hasActiveTransaction == false) {
            $this->hasActiveTransaction = true;
            if (is_null($this->masterServer)) {
                $this->getConnectionInstance();
            }

            $this->masterServer->beginTransaction();
        }

    }


    /**
     * 事务回滚
     * @return bool 成功返回true，失败返回false
     */
    public final function rollback()
    {
        if ($this->hasActiveTransaction) {
            $this->hasActiveTransaction = false;
            $this->transStatus = false;
            if (is_null($this->masterServer)) {
                $this->getConnectionInstance();
            }

            $this->masterServer->rollback();
        }
    }

    /**
     * 提交事务
     * @return bool 成功返回true，失败返回false
     */
    public final function commit()
    {
        if ($this->hasActiveTransaction) {
            $this->transStatus = true;
            if (is_null($this->masterServer)) {
                $this->getConnectionInstance();
            }

            $this->masterServer->commit();
        }

        $this->hasActiveTransaction = false;
    }


    /**
     * 启动事务处理模式
     * @return bool 成功返回true，失败返回false
     */

    public final function transaction($callback)
    {
        try {
            $this->startTrans();

            $callback();
            $this->commit();

        } catch (\Exception $err) {
            $this->rollback();
            //   throw  new \Exception($err->getMessage());

        } finally {
            return $this;
        }


    }


    /**
     * 获取最后一次插入的自增值
     * @return bool|int 成功返回最后一次插入的id，失败返回false
     */
    public final function getLastId()
    {
        return $this->masterServer->getLastId();
    }


    public function __destruct()
    {
        $this->cleanLastSql();

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

        $sql = "desc $this->escapeTableName";

        $info = $this->query($sql)->result();


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
    function addField($info = [], $tableName = '', $auto = true)
    {
        $this->table($tableName, $auto);


        $sql = "alter table {$this->escapeTableName} add ";
        if (!$field = $this->filterFieldInfo($info)) return false;
        $sql .= $field;

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
    function editField($info = [], $tableName = '', $auto = true)
    {

        $this->table($tableName, $auto);

        $sql = "alter table {$this->escapeTableName} modify ";

        if (!$field = $this->filterFieldInfo($info)) return false;
        $sql .= $field;


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
    private function filterFieldInfo($info = [])
    {
        if (!is_array($info)) return false;

        $newInfo = [];
        $newInfo['name'] = $info['name'];
        $newInfo['type'] = strtolower($info['type']);
        switch ($newInfo['type']) {
            case 'varchar':
            case 'char':
                $newInfo['length'] = !isset($info['length']) ? 255 : $info['length'];
                $newInfo['default'] = 'DEFAULT "' . (isset($info['default']) ? $info['default'] : '') . '"';
                break;
            case 'text':
            case 'longtext':
            case 'date':
            case 'datetime':
            case 'timestamp':
                $newInfo['length'] = '';
                $newInfo['default'] = '';
                break;
            case 'tinyint':
            case 'int':
            case 'bigint':
                $newInfo['length'] = !isset($info['length']) ? null : $info['length'];
                $newInfo['default'] = 'DEFAULT ' . (isset($info['default']) ? (int)$info['default'] : 0);
                break;

            case 'float':
            case 'double':
            case 'decimal':
                $newInfo['length'] = !isset($info['length']) ? '10,2' :
                    ((is_array($info['length']) && count($info['length']) == 2 && $info['length'][0] > $info['length'][1]) ? implode(',', $info['length']) : '10,2');

                $newInfo['default'] = 'DEFAULT ' . (isset($info['default']) ? (int)$info['default'] : 0);
                break;

            case 'enum':
                if (!is_array($info['value']) || empty($info['value'])) return false;

                $newInfo['length'] = implode(',', array_map(function ($item) {
                    return "'{$item}'";
                }, $info['value']));
                $newInfo['default'] = 'DEFAULT "' . (isset($info['default']) ? $info['default'] : reset($info['value'])) . '"';
                break;
        }
        $newInfo['isNull'] = !empty($info['isNull']) ? ' NULL ' : ' NOT NULL ';
        $newInfo['comment'] = isset($info['comment']) ? ' ' : ' COMMENT "' . $info['comment'] . '" ';

        $sql = $this->escapeId($newInfo['name']) . ' ' . $newInfo['type'];
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
    function getFieldInfo($field = [], $tableName = '', $auto = true)
    {
        $this->table($tableName, $auto);

        $info = [];
        if (is_string($field)) {
            $field = explode(',', $field);
        }

        foreach ($field as $v) {
            $info[$v] = $this->checkField($v);
        }

        return $info;
    }


}