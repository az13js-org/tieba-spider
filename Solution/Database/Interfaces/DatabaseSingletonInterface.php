<?php
namespace Solution\Database\Interfaces;

/**
 * 数据库连接池
 *
 * 根据指定名称保存多个单例
 *
 * @author MengShaoying <mengshaoying@aliyun.com>
 */
interface DatabaseSingletonInterface
{
    /**
     * 根据给定名称返回单例
     *
     * @param string $address 数据库地址
     * @param string $user 数据库用户
     * @param string $port 端口
     * @param string $psw 密码
     * @param string $db 数据库库名
     * @param string $name 名称
     * @return DatabaseSingletonInterface 返回实例对象
     * @author MengShaoying <mengshaoying@aliyun.com>
     */
    public static function getInstance(string $address, string $user, string $port, string $psw, string $db);
}
