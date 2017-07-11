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
    protected static $object;
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

    /**
     * 默认表名称
     * @var null
     */
    protected $tableName = null;

    /**
     * 临时表名
     * @var null
     */
    protected $tempTableName = null;


    //要被预处理和执行的 SQL 语句
    protected $statement;
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

    protected static $tableFields = [];

    public function __construct($tableName = null, $connection = null)
    {
        if (defined('APP_MODE') && file_exists(APP_PATH . "Config/database_" . APP_MODE . ".php")) {
            $this->dbConfig = requireCache(APP_PATH . "Config/database_" . APP_MODE . ".php");
        } else {
            $this->dbConfig = requireCache(APP_PATH . "Config/database.php");
        }

        if ($tableName) {
            $this->tableName = $tableName;
        }
        if (!is_null($connection)) {
            $this->connection = $connection;
        } elseif (is_null($this->connection)) {
            $this->connection = $this->dbConfig['defaultConnection'];
        }

        $this->openCache = C('openCache');

        $this->tablePrefix = $this->dbConfig[$this->connection]['masterServer']['tablePrefix'];


    }


    public function connection($name)
    {
        $this->connection = $name;
        $this->tablePrefix = $this->dbConfig[$this->connection]['masterServer']['tablePrefix'];
        return $this;
    }

    public function getConnection()
    {
        return $this->connection;
    }

    public function getConnectionInstance()
    {
        $this->slaveServer = [];
        $dbConf = $this->dbConfig[$this->connection];
        $this->masterServer = static::getInstance($dbConf['masterServer']);

        if (empty($dbConf['slaveServer'])) {
            $this->slaveServer[] = $this->masterServer;
        } else {
            if (!is_array($dbConf['slaveServer'])) {
                $dbConf['slaveServer'] = array($dbConf['slaveServer']);
            }

            foreach ($dbConf['slaveServer'] as $v) {
                $this->slaveServer[] = static::getInstance($v);
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
        $startTime = microtime(true);
        $key = md5(json_encode($dbConfig));

        if (is_object(static::$object[$key])) {
            $obj = static::$object[$key];
        } else {
            if (empty($dbConfig['dsn'])) {
                $dbConfig['dsn'] = $dbConfig['dbType'] . ":host=" . $dbConfig['dbHost'] . ";port=" . $dbConfig['dbPort'] . ";dbname=" . $dbConfig['dbName'];
            }

            static::$object[$key] = Db\PdoDriver::getInstance($dbConfig);

            if (static::$object[$key] instanceof Db\IDBDriver) {
                $obj = static::$object[$key];
            } else {
                die('错误：必须实现db\Driver接口');
            }

            $endTime = microtime(true);
            Debug::addMsg(['sql' => '数据库连接时间(' . $dbConfig['dsn'] . ')', 'time' => Debug::spent($startTime, $endTime)], 2);
        }

        return $obj;
    }


    public function escapeId($field = '')
    {
        if (empty($field)) {
            return '';
        }

        if (is_array($field)) {
            return trim(array_reduce($field, function ($result, $item) {
                return $result . ',' . $this->escapeId($item);
            }), ',');
        } else {
            //$matches[1]为别名
            $matches = preg_split('/\s(as|\s+)\s*/', trim($field));
            $field = explode('.', $matches[0]);

            if (isset($field[1])) {
                $field = "`{$this->tablePrefix}{$field[0]}`." . (strpos($field[1], '*') === false ? "`{$field[1]}`" : $field[1]);
            } else {
                $field = strpos($field[0], '*') === false ? "`{$field[0]}`" : $field[0];
            }

            if (isset($matches[1])) {
                return $field . ' as `' . $matches[1] . '`';
            }
            return $field;
        }
    }

    public function escape($value = '')
    {
        if (is_array($value)) {
            if (Arr::isAssoc($value)) {
                $val = '';
                foreach ($value as $k => $v) {
                    $val .= $this->escapeId($k) . '=' . $this->escape($v) . ',';
                }
                return trim($val, ',');
            } else {
                return '(' . array_reduce($value, function ($result, $item) {
                    return $result . ($result ? ',' : '') . $this->escape($item);
                }) . ')';
            }

        } elseif (is_string($value)) {
            return '"' . trim($value) . '"';
        } elseif (is_numeric($value)) {
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
        $method = strtolower($method);
        if ($method == 'count') {
            $obj = $this;
            if (count($args)) {
                $auto = end($args) !== false;
                $obj = $obj->table($args[0], $auto);
            }
            return $obj->select($method . '(*) as c')->get()->row()->c;

        } elseif (in_array($method, array('sum', 'min', 'max', 'avg'))) {
            $obj = $this;
            switch (count($args)) {
                case 1:
                    $field = $args[0];
                    break;
                case 2:
                    $field = $args[0];
                    $obj = $obj->table($args[1], true);
                    break;
                case 3:
                    $field = $args[0];
                    $obj = $obj->table($args[1], (boolean)$args[2]);
                    break;
                default:
                    throw  new Exception('参数错误');
            }

            return $obj->select($method . '(' . $field . ') as c')->get()->row()->c;

        }
        return $this;
    }

    /**
     * @param string $where
     * @param string $logical
     * @return $this
     */
    public function where($where = '', $logical = "and")
    {
        return $this->condition($where, $logical);
    }

    /**
     * @param string $where
     * @param string $logical
     * @return $this
     */
    public function having($where = '', $logical = "and")
    {
        return $this->condition($where, $logical, 'having');
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
                    $logical = $filed[0];
                    $operator = $filed[1];
                    $filed = $this->escapeId($filed[2]);
                } elseif (preg_match('/(and|or)\s+/i', $k, $matches)) {
                    $logical = $matches[0];
                    $operator = '=';
                    $filed = $this->escapeId($filed[1]);
                } else {
                    $operator = isset($filed[1]) ? $filed[1] : '=';
                    $filed = $this->escapeId($filed[0]);
                }

                if ($count != 0) {
                    $this->methods[$type] .= " {$logical} ";
                }

                if ($v instanceof \Closure) {
                    $this->methods[$type] .= $filed . ' ' . $operator . ' (' . call_user_func($v, new Model($this->tableName)) . ')';
                } elseif (is_null($v) || strripos($v, 'null') !== false) {
                    $this->methods[$type] .= $filed . ' is null';
                } else {
                    $operator = strtoupper($operator);
                    if (is_string($v)) {
                        $v = explode(',', $v);
                    }

                    if (strpos($operator, 'IN') !== false) {
                        $val = $this->escape($v);
                    } elseif (strpos($operator, 'BETWEEN') !== false) {
                        $val = $this->escape($v[0]) . ' and  ' . $this->escape($v[1]);
                    } elseif (strpos($type, 'on') !== false) {
                        $val = $this->escapeId($v);
                    } else {
                        $val = $this->escape($v);
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
     * @param string $sql
     * @param string $method
     * @return $this
     */
    protected function orderOrGroup($sql = '', $method = 'order')
    {
        $args = array_filter(explode(',', trim($sql)));

        foreach ($args as $v) {
            if ($this->methods[$method] != "") {
                $this->methods[$method] .= ',';
            }

            $order = preg_split("/[\s,]+/", trim($v));
            $dot = explode('.', $order[0]);
            $this->methods[$method] .= '`' . $dot[0] . '`';

            if (isset($dot[1])) {
                $this->methods[$method] .= ".`$dot[1]`";
            }

            if (isset($order[1])) {
                $this->methods[$method] .= ' ' . $order[1];
            }

        }
        return $this;
    }

    /**
     * @param string $sql “id desc,createTime desc”=>“order by id desc,createTime desc”
     * @return Model
     */
    public function order($sql = '')
    {
        return $this->orderOrGroup($sql, 'order');
    }

    /**
     * @param string $sql “name,price”=>“group by name,price”
     * @return Model
     */
    public function group($sql = '')
    {
        return $this->orderOrGroup($sql, 'group');
    }


    /**
     * @param array $field
     * @return $this
     */
    public final function select($field = [])
    {
        if (func_num_args() > 1) {
            $field = func_get_args();
        }

        if (is_array($field)) {
            $fieldArr = $field;
        } else {
            $fieldArr = explode(',', $field);
        }

        $field = $this->escapeId($fieldArr);

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
        $this->setTempTableName($tableName, $auto);
        return $this;
    }


    protected final function setTempTableName($tableName = "", $auto = true)
    {
        if (empty($tableName)) {
            $tableName = $this->tableName;
        } elseif ($tableName instanceof \Closure) {
            return $this->tempTableName = ' (' . call_user_func($tableName, new Model($this->tableName)) . ') as tmp' . uniqid();
        }

        if ($auto && !empty($this->tablePrefix)) {
            $tableName = strpos($tableName, $this->tablePrefix) === false
                ? $this->tablePrefix . $tableName
                : $tableName;
        }

        $tableName = preg_split('/\s+|as/', $tableName);
        if (isset($tableName[1])) {
            $this->tempTableName = "`{$tableName[0]}` `{$this->tablePrefix}{$tableName[1]}`";
        } else {
            $this->tempTableName = "`{$tableName[0]}`";
        }

        return $this->tempTableName;
    }

    protected final function getTempTableName()
    {
        if (empty($this->tempTableName)) {
            $this->setTempTableName($this->tableName);
        }
        return $this->tempTableName;
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
        $field = $this->methods['field'] ? $this->methods['field'] : '*';
        $order = $this->methods["order"] != "" ? " ORDER BY {$this->methods["order"]} " : "";
        $group = $this->methods["group"] != "" ? " GROUP BY {$this->methods["group"]}" : "";
        $having = $this->methods["having"] != "" ? "{$this->methods["having"]}" : "";

        $this->statement = "SELECT $field FROM  {$this->getTempTableName()} ";

        foreach ((array)$this->methods['join'] as $v) {
            $this->statement .= " " . $v . " ";
        }

        $this->statement .= "{$this->methods['where']}{$group}{$having}{$order}{$this->methods['limit']}";
        $this->cleanLastSql();
        return $this->statement;
    }


    /**
     * @return $this
     */
    public final function get($tableName = "", $auto = true)
    {
        if ($tableName) {
            $this->setTempTableName($tableName, $auto);
        }

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
        $this->tempTableName = null;
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
        $tableAlias = isset($table[1]) ? $this->escapeId($this->tablePrefix . $table[1]) : '';
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

        $this->methods['join'][] = $type . 'JOIN ' . $table . ' ' . $tableAlias . $this->methods['on'];

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
        $getPreProcessCache = $this->getDataPreProcessAttr();

        if (!$assoc) {
            foreach ($getPreProcessCache as $fieldName => $method) {
                foreach ($data as $v) {
                    if (!isset($v->{$fieldName})) {
                        break;
                    }

                    $v->{$fieldName} = $this->$method($v->{$fieldName});
                }
            }
        } else {
            foreach ($getPreProcessCache as $fieldName => $method) {
                foreach ($data as $v) {
                    if (!isset($v[$fieldName])) {
                        break;
                    }

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
        $setPreProcessCache = $this->getDataPreProcessAttr('set');

        if (empty($setPreProcessCache)) {
            return false;
        } else {
            foreach ($setPreProcessCache as $fieldName => $method) {


                if (($key = array_search($fieldName, $filed)) === false) {
                    break;
                }


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
            $preProcessCaches = $this->getMutatedAttributes();

            if (!isset($preProcessCaches[$type])) {
                return [];
            }

            $getCache = $preProcessCaches[$type];

            $fields = $this->tableField();

            $getAttr = [];
            foreach ($getCache as $v) {
                if (
                    ($key = Arr::arrayISearch($v, $fields))
                    || ($key = Arr::arrayISearch(parseNaming($v, 2), $fields))
                ) {
                    $getAttr[$fields[$key]] = $type . $v . 'Attribute';
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

                if ($matches[1][$key] == 'get') {
                    $mutatedAttributes['get'][] = $match;
                } else {
                    $mutatedAttributes['set'][] = $match;
                }
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
        $dbCacheFile = $this->tableName . '_' . md5($this->statement) . '_' . $row . (int)$assoc . (int)$this->PreProcessStatus;

        $cache = Cache::getInstance();

        $openCacheBak = $this->openCache;

        $this->openCache = C('openCache');

        if ($openCacheBak && !$cache->isExpired($dbCacheFile)) {
            return $cache->get($dbCacheFile)->data;
        }

        $this->getConnectionInstance();
        $this->db = $this->slaveServer[array_rand($this->slaveServer, 1)];

        $query = $this->db->query($this->statement);
        $this->queries[] = $this->statement;

        if ($row == 'result') {
            $data = $query->result($assoc);
        } else {
            $data[] = $query->row($assoc);
        }

        if ($this->PreProcessStatus) {
            $re = $this->getDataPreProcessFill($data, $assoc);
        } else {
            $this->PreProcessStatus = true;
        }


        if ($openCacheBak) {
            $cache->set($dbCacheFile, (object)['sql' => $this->statement, 'data' => $re]);
        }

        return $re;

    }


    /**
     * @param bool|false $assoc 当该参数为 TRUE 时，将返回 array 而非 object 。
     * @return mixed
     */
    public final function row($assoc = false)
    {
        $re = $this->cache($assoc, 'row');

        if (isset($re[0])) {
            return $re[0];
        }

        return false;
    }


    /**
     * 返回数据集合
     * @param bool|false $assoc 当该参数为 TRUE 时，将返回 array 而非 object 。
     * @return mixed
     */
    public final function result($assoc = false)
    {
        return $this->cache($assoc, 'result');

    }


    /**
     * @param string $tableName //数据库表名
     * @param string|array $where 条件
     * @return int 返还受影响行数
     */
    public final function delete($where = "")
    {
        if (!empty($where)) {
            $this->where($where);
        }

        $where = $this->methods['where'];
        $limit = $this->methods['limit'];

        $this->statement = "DELETE FROM {$this->getTempTableName()} {$where} {$limit}";

        return $this->query($this->statement)->result();

    }

    /**
     * 添加数据 如果主键冲突 则修改
     * @param $data
     * @return bool|int
     */
    public function duplicateKey($data)
    {
        if (empty($data)) {
            $data = \request::post();
        }

        if (!$data) {
            return false;
        }
        $data = $this->check($data);

        if ($data === false) {
            return false;
        }

        $fields = array_keys($data);

        if ($this->PreProcessStatus) {
            $values = $this->setDataPreProcessFill($fields, $data);
            $data = $values === false ? $data : $values;
        } else {
            $this->PreProcessStatus = true;
        }


        $escapeData = $this->escape($data);

        $this->statement = 'INSERT  INTO ' . $this->getTempTableName() . ' set ' . $escapeData . ' on duplicate key update ' . $escapeData;

        $re = $this->query($this->statement, $data);

        return $re->getLastId();
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
        if (empty($data)) {
            $data = \request::post();
        }

        if (!$data) {
            return false;
        }

        $data = $this->check($data);

        if ($data === false) {
            return false;
        }

        return $this->inserts($data, $act);
    }

    /**
     * 添加单条数据 如已存在则替换
     * @param array $data 添加的数据
     * @param string $tableName 数据库表名
     * @param bool $auto 是否自动添加表前缀
     * @return int 受影响行数
     */
    function replace($data = [])
    {
        return $this->insert($data, 'REPLACE');
    }


    /**
     * 预处理，添加多条数据
     * @param array $data 添加的数据 单条：[filed=>val]| 多条：[[filed=>val],[filed=>val]]
     * @param string $act
     * @return int 受影响行数
     */
    function inserts($data = [], $act = 'INSERT')
    {
        if (is_array($data[0])) {
            $fields = array_keys($data[0]);
        } else {
            $fields = array_keys($data);
        }

        if ($this->PreProcessStatus) {
            $values = $this->setDataPreProcessFill($fields, $data);
            $data = $values === false ? $data : $values;
        } else {
            $this->PreProcessStatus = true;
        }


        $field = $this->escapeId($fields);

        //$value = trim(str_repeat('?,', count($fields)), ',');

        $value = trim(array_reduce($fields, function ($res, $item) {
            return $res . ',:' . $item;
        }), ',');

        $this->statement = "{$act}  INTO " . $this->getTempTableName() . "(" . $field . ")  VALUES(" . $value . ") ";


        $re = $this->query($this->statement, $data);

        return $re->getLastId();
    }

    /**
     * 预处理添加多条数据 如已存在则替换
     * @param array $filed 字段
     * @param array $data 添加的数据
     * @return int 受影响行数
     */
    function replaces($data = [])
    {
        return $this->inserts($data, 'REPLACE');
    }


    /**
     * @param  array $array 要验证的字段数据
     * @param  string $tableName 数据表名
     * @param bool $auto
     * @return array|bool
     */

    public final function check($array)
    {
        if ($this->_validate) {
            $tableField = $this->tableField();

            if ($tableField === false) {
                return false;
            }

            foreach ($array as $key => $value) {
                //判断字段是否存在 不存在则舍弃
                if (!Arr::inIArray($key, $tableField)) {
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
        if (isset(static::$tableFields[$this->tempTableName])) {
            return static::$tableFields[$this->tempTableName];
        }

        $sql = 'desc ' . $this->getTempTableName();
        $result = $this->query($sql)->result();

        foreach ($result as $row) {

            if ($row->Key == "PRI") {
                $fields["pri"] = $row->Field;
            } elseif ($row->Extra == "auto_increment") {
                $fields["auto"] = $row["Field"];
            } else {
                $fields[] = $row->Field;
            }

        }
        //如果表中没有主键，则将第一列当作主键
        if (isset($fields)) {
            if (!array_key_exists("pri", $fields)) {
                $fields["pri"] = array_shift($fields);
            }
            static::$tableFields[$this->tableName] = $fields;

            return static::$tableFields[$this->tableName];
        }

        return false;
    }

    /**
     * 返回受上一个 SQL 语句影响的行数
     * @return int
     */
    public final function rowCount()
    {
        if ($this->db === null) {
            return 0;
        }
        return $this->db->rowCount();

    }

    /**
     * @param string $sql
     * @param array $parameters array|''
     * @return $this|\PDOStatement
     */
    public final function query($sql = "", $parameters = [])
    {
        if (empty($sql)) {
            throw new Exception('SQL不能为空');
        }
        $this->statement = $sql;
        $this->queries[] = $this->statement;


        $this->parameters = !is_array($parameters) ? [] : $parameters;

        $this->getConnectionInstance();

        $this->cleanLastSql();
        if (stripos($sql, 'select') === false) {
            $this->queries[] = $sql;
            $this->db = $this->masterServer;

            return $this->db->query($this->statement, $parameters);
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
        if (empty($data)) {
            $data = \Request::post();
        }

        $data = $this->check($data);

        if (!$data) {
            return false;
        }

        if (!empty($where)) {
            $this->where($where);
        }

        $where = $this->methods['where'];
        $limit = $this->methods['limit'];

        if ($this->PreProcessStatus) {
            $fields = array_keys($data);
            $values = array_values($data);
            $values = $this->setDataPreProcessFill($fields, $values);
            $data = $values === false ? $data : $values;
        } else {
            $this->PreProcessStatus = true;
        }

        $data = $this->escape($data);

        $this->statement = "UPDATE " . $this->getTempTableName() . " SET " . $data . " " . $where . " " . $limit;

        return $this->query($this->statement)->result();
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
        if (!$this->hasActiveTransaction) {
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

            $callback($this);

            $this->commit();
            return true;
        } catch (\Exception $err) {
            $this->rollback();
            return (object)['code' => $err->getCode(), 'message' => $err->getMessage()];
        }
//        finally {
//            return $this;
//        }


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

        $sql = "CREATE TABLE IF NOT EXISTS {$this->tempTableName} (`$key` INT NOT NULL AUTO_INCREMENT  primary key) ENGINE = {$engine};";
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

        $sql = " DROP TABLE IF EXISTS $this->tempTableName";
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
        $sql = "desc $this->tempTableName";
        return $this->query($sql)->result();
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
        $sql = "desc {$this->tempTableName} $field";
        return $this->query($sql)->row();
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


        $sql = "alter table {$this->tempTableName} add ";
        if (!$field = $this->filterFieldInfo($info)) {
            return false;
        }

        $sql .= $field;
        return $this->query($sql)->result;
    }


    /**
     * 修改字段
     * 不能修改字段名称，只能修改字段类型、默认值、注释
     * @param array $info
     * @param string $tableName
     * @param bool $auto 是否自动添加表前缀
     * @return mixed
     */
    function editField($info = [], $tableName = '', $auto = true)
    {

        $this->table($tableName, $auto);

        $sql = "alter table {$this->tempTableName} modify ";

        if (!$field = $this->filterFieldInfo($info)) {
            return false;
        }

        $sql .= $field;
        return $this->query($sql)->result;
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
        if (!is_array($info)) {
            return false;
        }

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
                if (!is_array($info['value']) || empty($info['value'])) {
                    return false;
                }

                $newInfo['length'] = implode(',', array_map(function ($item) {
                    return "'{$item}'";
                }, $info['value']));
                $newInfo['default'] = 'DEFAULT "' . (isset($info['default']) ? $info['default'] : reset($info['value'])) . '"';
                break;
            default:
                return false;
        }
        $newInfo['isNull'] = !empty($info['isNull']) ? ' NULL ' : ' NOT NULL ';
        $newInfo['comment'] = isset($info['comment']) ? ' ' : ' COMMENT "' . $info['comment'] . '" ';

        $sql = $this->escapeId($newInfo['name']) . ' ' . $newInfo['type'];
        $sql .= (!empty($newInfo['length'])) ? '(' . $newInfo['length'] . ')' . " " : ' ';

        $sql .= $newInfo['isNull'];
        $sql .= $newInfo['default'];

        return $sql . $newInfo['comment'];
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

        $sql = "alter table {$this->tempTableName} drop column $field";
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