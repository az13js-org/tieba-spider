<?php
namespace Solution\AnythingObject;

use Solution\AnythingObject\Abstracts\AbstractAnythingObjectStruct;
use Solution\AnythingObject\Interfaces\ListenerInterface;
use Solution\AnythingObject\Interfaces\SingletonListenerForStructInterface;
use Solution\AnythingObject\Interfaces\ListenableInterface;
use Solution\Database\Connect as DBTableStruct;

/**
 * 对象结构
 *
 * 只保存对象的结构
 * @author MengShaoying <mengshaoying@aliyun.com>
 */
class AnythingObjectStruct extends AbstractAnythingObjectStruct implements ListenerInterface, SingletonListenerForStructInterface, ListenableInterface
{
    /** @var string 对应的对象名称 */
    private $objectName = '';

    /**
     * 属性列表。
     *
     * 一维数组，内容为存在的属性的名称
     *
     * @var string[]
     */
    private $properties = [];

    /** @var AnythingObjectInterface[] 监听的对象 */
    private $listenables = [];

    /** @var array 数组，key是实例名，值是对象 */
    private static $instances = [];

    /** @var DBTableStruct 数据库表结构监听者 */
    private $tableStructs = [];

    /**
     * 根据给定名称返回单例
     *
     * @param string $name 名称
     * @return AnythingObjectStruct 返回实例对象
     * @author MengShaoying <mengshaoying@aliyun.com>
     */
    public static function getInstance(string $name): AnythingObjectStruct
    {
        if (isset(self::$instances[$name])) {
            return self::$instances[$name];
        }
        self::$instances[$name] = new static();
        self::$instances[$name]->setObjectName($name);
        DBTableStruct::getInstance(
            '127.0.0.1',
            'root',
            '3306',
            '11111111',
            'test_db1'
        )->bindObject(self::$instances[$name]);
        return self::$instances[$name];
    }

    /**
     * 设置对象名称
     *
     * @param string $name
     * @return void
     * @author MengShaoying <mengshaoying@aliyun.com>
     */
    public function setObjectName(string $name)
    {
        $this->objectName = $name;
    }

    /**
     * 获取对象名称
     *
     * @return string 对象名
     * @author MengShaoying <mengshaoying@aliyun.com>
     */
    public function getObjectName(): string
    {
        return $this->objectName;
    }

    /**
     * 返回当前的结构
     *
     * @return array
     * @author MengShaoying <mengshaoying@aliyun.com>
     */
    public function getStruct(): array
    {
        return $this->properties;
    }

    /**
     * 添加属性
     *
     * @param string $propertyName 属性名称
     * @return bool 添加成功返回true,失败返回false。属性存在时返回false
     * @author MengShaoying <mengshaoying@aliyun.com>
     */
    public function addProperty(string $propertyName): bool
    {
        if (in_array($propertyName, $this->properties)) {
            return false;
        }
        $this->properties[] = $propertyName;
        $this->notifyAll();
        return true;
    }

    /**
     * 此方法将在监听对象被改变的时候调用
     *
     * @return void
     * @see ListenerInterface
     * @author MengShaoying <mengshaoying@aliyun.com>
     */
    public function update()
    {
        foreach ($this->listenables as $listenable) {
            foreach ($listenable->getPropertiesExists() as $property) {
                $this->addProperty($property);
            }
        }
    }

    /**
     * 通知所有监听者自身改变的事件
     *
     * @param AnythingObject $obj 被监听的对象
     * @return void
     * @see ListenerInterface
     * @author MengShaoying <mengshaoying@aliyun.com>
     */
    public function bindObject($obj)
    {
        $this->listenables[] = $obj;
        $obj->addListener($this);
    }

    /**
     * 添加监听此对象变化的监听者
     *
     * @param DBTableStruct $listener 监听者
     * @return void
     * @author MengShaoying <mengshaoying@aliyun.com>
     */
    public function addListener($listener)
    {
        $this->tableStructs[] = $listener;
    }

    /**
     * 通知所有监听者自身改变的事件
     *
     * @return void
     * @author MengShaoying <mengshaoying@aliyun.com>
     */
    public function notifyAll()
    {
        foreach ($this->tableStructs as $listener) {
            $listener->update();
        }
    }
}
