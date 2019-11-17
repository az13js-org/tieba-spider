<?php
namespace Solution\AnythingObject\Interfaces;

/**
 * 观察者模式
 *
 * 可被监听对象
 *
 * @author MengShaoying <mengshaoying@aliyun.com>
 */
interface ListenableInterface
{
    /**
     * 添加监听此对象变化的监听者
     *
     * @param object $server 监听者
     * @return mixed
     * @author MengShaoying <mengshaoying@aliyun.com>
     */
    public function addListener($server);

    /**
     * 通知所有监听者自身改变的事件
     *
     * @return mixed
     * @author MengShaoying <mengshaoying@aliyun.com>
     */
    public function notifyAll();
}
