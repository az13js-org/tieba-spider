<?php
namespace Solution\Database\Interfaces;

/**
 * 连接
 *
 * @author MengShaoying <mengshaoying@aliyun.com>
 */
interface ConnectionInterface
{
    /**
     * 发送数据
     *
     * @param string $data 发送的数据
     * @return bool
     * @author MengShaoying <mengshaoying@aliyun.com>
     */
    public function send(string $data): bool;

    /**
     * 接受数据
     *
     * @return mixed
     * @author MengShaoying <mengshaoying@aliyun.com>
     */
    public function recv();
}
