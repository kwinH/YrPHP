<?php
/**
 * Created by YrPHP.
 * User: Kwin
 * QQ:284843370
 * Email:kwinwong@hotmail.com
 * GitHub:https://github.com/kwinH/YrPHP
 */
namespace YrPHP\Db;

use Exception;
use PDO;
use PDOException;
use PDOStatement;
use YrPHP\Debug;

class PdoDriver extends PDO implements IDBDriver
{
    // 当前数据库操作对象
    public static $_instance = array();

    /**
     *  PDO操作实例
     * @var \PDOStatement
     */
    public $PDOStatement = null;
    public $sql;
    public $result = false;


    public function __construct($dsn, $username, $passwd, $options = array())
    {
        parent::__construct($dsn, $username, $passwd, $options = array());
    }


    /**
     * 构造mysql对象供外部调用的方法
     * @param array $dbConfig mysql的配置数组信息
     * @return object 构造出来的mysql对象
     */
    public static function getInstance($dbConfig = null)
    {
        $key = md5(serialize($dbConfig));
        if (!isset(self::$_instance[$key])) {
            self::$_instance[$key] = null;
        }

        if (!(self::$_instance[$key] instanceof self)) {
            if (empty($dbConfig['dsn'])) {
                $dsn = $dbConfig['dbType'] . ":host=" . $dbConfig['dbHost'] . ";port=" . $dbConfig['dbPort'] . ";dbname=" . $dbConfig['dbName'];
            } else {
                $dsn = $dbConfig['dsn'];
            }
            try {
                // throw new PDOException("error");//错误抛出异常
                self::$_instance[$key] = new self($dsn, $dbConfig['dbUser'], $dbConfig['dbPwd']);
                self::$_instance[$key]->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);// 设置为异常模式)))

                if ($dbConfig['dbType'] == 'mysql' || $dbConfig['dbType'] == 'pgsql') {
                    self::$_instance[$key]->exec("SET NAMES '{$dbConfig['charset']}'");
                }
            } catch (PDOException $e) {
                die ("连接数库失败：" . $e->getMessage());
            }
        }

        return self::$_instance[$key];

    }

    /**
     * 处理sql语句
     * @param $sql
     * @param array $parameters
     * @return mixed
     */
    function query($sql, $parameters = array())
    {
        $errorMsg = '';
        Debug::start();

        $this->sql = $sql;
        try {
            $this->PDOStatement = parent::prepare($sql);
            if (empty($parameters)) {
                $this->result = $this->PDOStatement->execute();
            } else {
                if (is_array($parameters[0])) {

                    foreach ($parameters as $v) {
                        $this->result = $this->PDOStatement->execute($v);
                    }

                } else {
                    $this->result = $this->PDOStatement->execute($parameters);
                }
            }

        } catch (PDOException $e) {
            echo '<pre>';
            $errorMsg = $e->getMessage();
            $errorSql = 'ERROR SQL: ' . $sql . PHP_EOL . $errorMsg;

            throw  new Exception($errorSql, $e->getCode());

        } finally {
            Debug::stop();
            Debug::addMsg(array('sql' => trim($sql) . ($parameters ? json_encode($parameters) : ''), 'time' => Debug::spent(), 'error' => $errorMsg), 2);
        }

        return $this;
    }


    /**
     * @param bool|false $assoc 当该参数为 TRUE 时，将返回 array 而非 object 。
     * @return bool|array|object
     */
    function row($assoc = false)
    {
        try {
            if (!$this->PDOStatement instanceof PDOStatement) {
                throw  new Exception('errors');
            } elseif ($assoc) {
                return $this->PDOStatement->fetch(PDO::FETCH_ASSOC);
            } else {
                return $this->PDOStatement->fetch(PDO::FETCH_OBJ);
            }
        } catch (Exception $e) {
            return $this->result;
        }

    }


    /**
     * @brief   返回SQL查询所有
     * @param bool|false $assoc 当该参数为 TRUE 时，将返回 array 而非 object 。
     * @return bool|array|object 如果是增加修改删除操作 则 return bool
     *                    否则返回一个对象数组，失败则返回一个空数组
     */
    function result($assoc = false)
    {
        try {
            if (!$this->PDOStatement instanceof PDOStatement) {
                throw  new Exception('errors');
            } elseif ($assoc) {
                return $this->PDOStatement->fetchAll(PDO::FETCH_ASSOC);
            } else {
                return $this->PDOStatement->fetchAll(PDO::FETCH_OBJ);
            }
        } catch (Exception $e) {
            return $this->result;
        }
    }

    /**
     * 获取最后一次插入的自增值
     * @return bool|int 成功返回最后一次插入的id，失败返回false
     */
    public function getLastId()
    {
        return parent::lastInsertId();
    }

    /**
     * 返回受上一个 SQL 语句影响的行数
     * @return bool
     */
    public function rowCount()
    {
        if (empty($this->PDOStatement)) {
            return false;
        }
        return $this->PDOStatement->rowCount();
    }

    public function getSql()
    {
        return $this->sql;
    }

}