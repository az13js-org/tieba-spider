<?php
namespace Solution\Database;

use \PDO;
use \PDOException;
use Solution\Database\Interfaces\DatabaseSingletonInterface;
use Solution\Database\Exceptions\DatabaseConnectException;
use Solution\AnythingObject\Interfaces\ListenerInterface;

/**
 * 数据库连接对象
 *
 * 代表一个数据库连接的实体
 *
 * @see DatabaseSingletonInterface 此类实现了这个接口
 * @author MengShaoying <mengshaoying@aliyun.com>
 */
class Connect implements DatabaseSingletonInterface, ListenerInterface
{
    /** @var PDO */
    protected $pdo = null;

    /** @var string */
    private $address = '';

    /** @var string */
    private $user = '';

    /** @var string */
    private $port = '';

    /** @var string */
    private $psw = '';

    /** @var string */
    private $db = '';

    /** @var array */
    private static $instances = [];

    /** @var \Solution\AnythingObject\AnythingObjectStruct[] 被监听对象 */
    private $anythingObjects = [];

    /** @var array 键值分别表示表名和存在的字段 */
    private $tables = [];

    /**
     * 预处理语句
     *
     * @var array
     */
    private $prepares = [];

    /**
     * 构造方法
     *
     * @param string $address 数据库地址
     * @param string $user 数据库用户
     * @param string $port 端口
     * @param string $psw 密码
     * @param string $db 数据库库名
     * @author MengShaoying <mengshaoying@aliyun.com>
     */
    public function __construct(string $address, string $user, string $port, string $psw, string $db)
    {
        $this->address = $address;
        $this->user = $user;
        $this->port = $port;
        $this->psw = $psw;
        $this->db = $db;
        try {
            $this->pdo = new PDO("mysql:host=$address;dbname=$db;port=$port;charset=utf8mb4", $user, $psw);
        } catch (PDOException $e) {
            throw new DatabaseConnectException('Connect fail.');
        }
    }

    /**
     * 根据给定名称返回单例
     *
     * @param string $address 数据库地址
     * @param string $user 数据库用户
     * @param string $port 端口
     * @param string $psw 密码
     * @param string $db 数据库库名
     * @return Connect 返回实例对象
     * @author MengShaoying <mengshaoying@aliyun.com>
     */
    public static function getInstance(string $address, string $user, string $port, string $psw, string $db): Connect
    {
        $key = hash('sha256', serialize([$address, $user, $port, $psw, $db]));
        if (isset(self::$instances[$key])) {
            return self::$instances[$key];
        }
        return self::$instances[$key] = new static($address, $user, $port, $psw, $db);
    }

    /**
     * 此方法将在监听对象被改变的时候调用
     *
     * @return void
     * @author MengShaoying <mengshaoying@aliyun.com>
     */
    public function update()
    {
        foreach ($this->anythingObjects as $anythingObjectStruct) {
            $this->syncStruct($anythingObjectStruct->getObjectName(), $anythingObjectStruct->getStruct());
        }
    }

    /**
     * 通知所有监听者自身改变的事件
     *
     * @param \Solution\AnythingObject\AnythingObjectStruct $obj 被监听的对象
     * @return void
     * @author MengShaoying <mengshaoying@aliyun.com>
     */
    public function bindObject($obj)
    {
        $this->anythingObjects[] = $obj;
        $obj->addListener($this);
    }

    /**
     * 比较并同步表结构
     *
     * @param string $table 表名称
     * @param array $columns 所有的字段
     * @return void
     * @author MengShaoying <mengshaoying@aliyun.com>
     */
    private function syncStruct(string $table, array $columns)
    {
        if (!isset($this->tables[$table])) {
            $this->tables[$table] = $this->tryGetTable($table);
            if (empty($this->tables[$table])) {
                $this->createTableWith($table, $columns);
                $this->tables[$table] = $columns;
            }
        }
        $alters = [];
        foreach ($columns as $column) {
            if (!in_array($column, $this->tables[$table])) {
                $alters[] = $column;
                $this->tables[$table][] = $column;
            }
        }
        if (!empty($alters)) {
            $this->alterTable($table, $alters);
        }
    }

    /**
     * 前置操作 获取给定的表的所有字段名称
     *
     * @return void
     * @author MengShaoying <mengshaoying@aliyun.com>
     */
    private function beforeTryGetTable()
    {
        $sql = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME=? AND TABLE_SCHEMA=?";
        if (!isset($this->prepares['tryGetTable'])) {
            $this->prepares['tryGetTable'] = $this->pdo->prepare($sql);
        }
    }

    /**
     * 获取给定的表的所有字段名称
     *
     * @param string $table 表名称
     * @return array 表字段名称，表不存在或无字段则返回空数组
     * @author MengShaoying <mengshaoying@aliyun.com>
     */
    private function tryGetTable(string $table): array
    {
        $this->beforeTryGetTable();
        $this->prepares['tryGetTable']->execute([$table, $this->db]);
        return array_column($this->prepares['tryGetTable']->fetchAll(PDO::FETCH_NUM), 0);
    }

    /**
     * 创建表
     *
     * @param string $table 表名称
     * @param array $columns 表字段名称
     * @return void
     * @author MengShaoying <mengshaoying@aliyun.com>
     */
    private function createTableWith(string $table, array $columns)
    {
        $sql = 'CREATE TABLE `' . $table . '`(';
        foreach ($columns as $column) {
            switch ($column) {
                case 'id':
                    $sql .= '`id` INT NOT NULL /**AUTO_INCREMENT**/,';
                    break;
                case 'version':
                    $sql .= '`version` INT NOT NULL DEFAULT 0,';
                    break;
                default:
                    $sql .= '`' . $column . '` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT "",';
                    break;
            }
        }
        $sql .= 'PRIMARY KEY(`id`)) ENGINE=InnoDB CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci';
        $this->pdo->exec($sql);
    }

    /**
     * 扩展表
     *
     * @param string $table 表名称
     * @param array $columns 表字段名称
     * @return void
     * @author MengShaoying <mengshaoying@aliyun.com>
     */
    private function alterTable(string $table, array $columns)
    {
        $sql = 'ALTER TABLE `' . $table . '` ';
        $add = '';
        foreach ($columns as $column) {
            if (!empty($add)) {
                $add .= ',';
            }
            switch ($column) {
                case 'id':
                    $add .= 'ADD `id` INT NOT NULL /**AUTO_INCREMENT**/';
                    break;
                case 'version':
                    $add .= 'ADD `version` INT NOT NULL DEFAULT 0';
                    break;
                default:
                    $add .= 'ADD `' . $column . '` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT ""';
                    break;
            }
        }
        $sql = $sql . $add . ';';
        $this->pdo->exec($sql);
    }
}
