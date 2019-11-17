<?php
namespace Solution\AnythingObject\Interfaces;

/**
 * 观察者模式
 *
 * 监听者
 *
 * @author MengShaoying <mengshaoying@aliyun.com>
 */
interface ListenerInterface
{
    /**
     * 此方法将在监听对象被改变的时候调用
     *
     * @return mixed
     * @author MengShaoying <mengshaoying@aliyun.com>
     */
    public function update();

    /**
     * 通知所有监听者自身改变的事件
     *
     * @param object $obj 被监听的对象
     * @return mixed
     * @author MengShaoying <mengshaoying@aliyun.com>
     */
    public function bindObject($obj);
}
