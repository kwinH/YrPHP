<?php
/**
 * Created by YrPHP.
 * User: Kwin
 * QQ: 284843370
 * Email: kwinwong@hotmail.com
 * GitHub: https://GitHubhub.com/quinnfox/yrphp
 */
namespace YrPHP\Db;


interface IDBDriver
{

    /**
     * 构造mysql对象供外部调用的方法
     * @param array $dbConfig mysql的配置数组信息
     * @return object 构造出来的mysql对象
     */
    static function getInstance($dbConfig = null);

    /**
     * 处理sql语句
     * @param $sql
     * @param array $parameters
     * @return mixed
     */
    function query($sql, $parameters = array());

    /**
     * 启动事务处理模式
     * @return bool 成功返回true，失败返回false
     */
    public function startTrans();

    /**
     * 提交事务
     * @return bool 成功返回true，失败返回false
     */
    public function commit();

    /**
     * 事务回滚
     * @return bool 成功返回true，失败返回false
     */
    public function rollback();

    /**
     * @param bool|false $assoc 当该参数为 TRUE 时，将返回 array 而非 object 。
     * @return bool|array|object
     */
    function row($assoc = false);

    /**
     * @brief   返回SQL查询所有
     * @param bool|false $assoc 当该参数为 TRUE 时，将返回 array 而非 object 。
     * @return bool|array|object 如果是增加修改删除操作 则 return bool
     *                    否则返回一个对象数组，失败则返回一个空数组
     */
    function result($assoc = false);

    /**
     * 获取最后一次插入的自增值
     * @return bool|int 成功返回最后一次插入的id，失败返回false
     */
    public function getLastId();

    /**
     * 返回受上一个 SQL 语句影响的行数
     * @return bool
     */
    public function rowCount();



}