<?php
/**
 * Created by YrPHP.
 * User: Kwin
 * QQ:284843370
 * Email:kwinwong@hotmail.com
 * GitHub:https://github.com/kwinH/YrPHP
 */
namespace YrPHP;


class Pipeline
{
    /**
     * 需要在流水线中处理的对象
     * @var
     */
    private $passable;

    /**
     * 处理者
     * @var
     */
    private $pipes;

    /**
     * 触发的方法
     * @var string
     */
    private $method = 'handler';

    public function __construct()
    {

    }

    /**
     * 设置需要处理的对象
     * @param $request
     * @return $this
     */
    public function send($request)
    {
        $this->passable = $request;
        return $this;
    }

    /**
     * 需要经过哪些中间件处理
     * @param $pipes
     * @return $this
     */
    public function through($pipes)
    {
        $this->pipes = is_array($pipes) ? $pipes : func_get_args();
        return $this;
    }

    /**
     * 开始流水线处理
     * @param \Closure
     * @return \Closure
     */
    public function then(\Closure $first)
    {
        return call_user_func(
            array_reduce(array_reverse($this->pipes), $this->getSlice(), $first),
            $this->passable);
    }

    /**
     * 包装迭代对象到闭包
     * @return \Closure
     */
    public function getSlice()
    {
        return function ($stack, $pipe) {
            return function ($passable) use ($stack, $pipe) {
                if ($stack instanceof \Closure) {
                    $pipe = loadClass($pipe);

                    if ($pipe instanceof IMiddleware) {
                        return call_user_func([$pipe, $this->method], $passable, $stack);
                    }
                }
                return null;
            };
        };
    }

}