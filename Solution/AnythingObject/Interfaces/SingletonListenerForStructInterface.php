<?php
namespace Solution\AnythingObject\Interfaces;

/**
 * 单例模式
 *
 * 根据指定名称保存多个单例
 *
 * @author MengShaoying <mengshaoying@aliyun.com>
 */
interface SingletonListenerForStructInterface
{
    /**
     * 根据给定名称返回单例
     *
     * @param string $name 名称
     * @return SingletonListenerForStructInterface 返回实例对象
     * @author MengShaoying <mengshaoying@aliyun.com>
     */
    public static function getInstance(string $name);
}
