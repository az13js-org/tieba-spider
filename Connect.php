<?php
use Solution\Database\Connect as BaseConnect;
use Solution\Database\Interfaces\ConnectionInterface;

/**
 * 数据库连接
 *
 * @author MengShaoying <mengshaoying@aliyun.com>
 */
class Connect extends BaseConnect implements ConnectionInterface
{
    /** @var mixed 存储接收到的数据 */
    private $recvData = null;

    /**
     * 发送数据
     *
     * @param string $data 发送的数据
     * @return bool
     * @author MengShaoying <mengshaoying@aliyun.com>
     */
    public function send(string $data): bool
    {
        $stm = $this->pdo->query($data);
        if (false === $stm) {
            return false;
        }
        $this->recvData = $stm->fetchAll(PDO::FETCH_ASSOC);
        return true;
    }

    /**
     * 接受数据
     *
     * @return mixed
     * @author MengShaoying <mengshaoying@aliyun.com>
     */
    public function recv()
    {
        return $this->recvData;
    }
}
