<?php
/**
 * Created by YrPHP.
 * User: Kwin
 * QQ: 284843370
 * Email: kwinwong@hotmail.com
 * GitHub: https://github.com/kwinH/YrPHP
 */
namespace YrPHP\Session;

use SessionHandlerInterface;
use YrPHP\Config;

class Memcached implements SessionHandlerInterface
{
    public $handler;

    /**
     * session_set_save_handler open方法
     * 在运行session_start()时执行
     * @param $savePath
     * @param $sessionName
     * @return true
     */
    public function open($savePath, $sessionName)
    {
        $this->handler = \YrPHP\Cache\Memcached::getInstance();
        return true;
    }

    /**
     * session_set_save_handler close方法
     * 在脚本执行完成 或 调用session_write_close() 或 session_destroy()时被执行，即在所有session操作完后被执行
     * @return bool
     */
    public function close()
    {
        return true;
    }

    /**
     * 读取session_id
     * 在运行session_start()时执行，因为在session_start时，会去read当前session数据
     * session_set_save_handler read方法
     * @return string 读取session_id
     */
    public function read($sessionId)
    {
        return $this->handler->get(Config::get('session.name') . $sessionId);
    }

    /**
     * 写入session_id 的值
     * 此方法在脚本结束和使用session_write_close()强制提交SESSION数据时执行
     * @param $sessionId 会话ID
     * @param $data 值
     * @return mixed query 执行结果
     */
    public function write($sessionId, $data)
    {
        return $this->handler->set(Config::get('session.name') . $sessionId, $data, Config::get('session.expire'));
    }

    /**
     * 删除指定的session_id
     * 在运行session_destroy()时执行
     * @param string $sessionId 会话ID
     * @return bool
     */
    public function destroy($sessionId)
    {
        $this->handler->delete(Config::get('session.name') . $sessionId);
    }

    /**
     * 删除过期的 session
     * 执行概率由session.gc_probability 和 session.gc_divisor的值决定，时机是在open，read之后，session_start会相继执行open，read和gc
     * @param $lifetime session有效期（单位：秒）
     * @return bool
     */
    public function gc($lifetime)
    {
        return true;
    }
}