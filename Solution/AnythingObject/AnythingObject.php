<?php
namespace Solution\AnythingObject;

use Solution\AnythingObject\Interfaces\AnythingObjectInterface;
use Solution\AnythingObject\Exceptions\AnythingObjectPropertyException;
use Solution\AnythingObject\Interfaces\ListenableInterface;

/**
 * 任意对象
 *
 * 用来表示一个具有任意属性的对象。
 *
 * @see AnythingObjectInterface 此类实现了这个接口
 * @author MengShaoying <mengshaoying@aliyun.com>
 */
class AnythingObject implements AnythingObjectInterface, ListenableInterface
{
    /** @var string 此对象被赋予的名称，使用 setObjectName 可以设置 */
    private $objectName = '';

    /** @var AnythingObjectFactory 产生任意对象的工厂，通过 bindObjectFactory 设置 */
    private $objectFactory = null;

    /** @var ListenerInterface[] 实现了监听服务器接口的监听者对象 */
    private $listeners = [];

    /**
     * 属性列表。使用 getProperty 和 setProperty 进行操作产生的属性将会保存在这里。
     *
     * 键值分别表示属性名称和属性的值。
     *
     * @var string[]
     */
    private $properties = [];

    /**
     * 设置对象名称
     *
     * @param string $name
     * @return void
     * @see AnythingObjectInterface
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
     * @see AnythingObjectInterface
     * @author MengShaoying <mengshaoying@aliyun.com>
     */
    public function getObjectName(): string
    {
        return $this->objectName;
    }

    /**
     * 绑定产生任意对象的工厂，以便于内部使用这个对象
     *
     * 使用 getMeny 和 getOne 之类的返回对象的方法时，内部需要一个工厂用来产生新的任意对象。
     *
     * @param AnythingObjectFactory $objectFactory
     * @return void
     * @see AnythingObjectInterface
     * @author MengShaoying <mengshaoying@aliyun.com>
     */
    public function bindObjectFactory($objectFactory)
    {
        $this->objectFactory = $objectFactory;
    }

    /**
     * 获取某个属性
     *
     * @param string $propertyName 属性名称
     * @return string 相信使用它的人获取到的任何属性都可以用文本显示
     * @throws AnythingObjectPropertyException
     * @see AnythingObjectInterface
     * @author MengShaoying <mengshaoying@aliyun.com>
     */
    public function getProperty(string $propertyName): string
    {
        if (empty($propertyName)) {
            throw new AnythingObjectPropertyException(
                'You want to get a property, but the name you specify is empty. '
                . "Name: \"$propertyName\"."
            );
        }
        if (isset($this->properties[$propertyName])) {
            return $this->properties[$propertyName];
        }
        $this->properties[$propertyName] = '';
        $this->notifyAll();
        return $this->properties[$propertyName];
    }

    /**
     * 设置某个属性
     *
     * @param string $propertyName 属性名称
     * @param mixed $val 所设置的属性的值
     * @return void
     * @see AnythingObjectInterface
     * @author MengShaoying <mengshaoying@aliyun.com>
     */
    public function setProperty(string $propertyName, string $val)
    {
        if (isset($this->properties[$propertyName])) {
            $this->properties[$propertyName] = $val;
        } else {
            $this->properties[$propertyName] = $val;
            $this->notifyAll();
        }
    }

    /**
     * 获取当前存在的所有属性
     *
     * @return string[]
     * @author MengShaoying <mengshaoying@aliyun.com>
     */
    public function getPropertiesExists(): array
    {
        return array_keys($this->properties);
    }

    /**
     * 获取多个其他产品对象
     *
     * @param string $objectName 对象名称
     * @return AnythingObject[]
     * @see AnythingObjectInterface
     * @author MengShaoying <mengshaoying@aliyun.com>
     */
    public function getMany(string $objectName): array
    {
        $result = [];
        for ($i = 0; $i < 3; ++$i) {
            //$anthingObject = $this->objectFactory->find($objectName, );
            //$anthingObject->setProperty($this->name . 'Id', $i);
            //$result[] = $anthingObject;
        }
        return $result;
    }

    /**
     * 获取父对象
     *
     * 上层对象可以用 getMany 取得包含自己的数组，此方法反过来获取上层对像
     *
     * @param string $objectName 父对象名称
     * @return AnythingObject
     * @see AnythingObjectInterface
     * @author MengShaoying <mengshaoying@aliyun.com>
     */
    public function getParent(string $objectName): AnythingObject
    {
        return new static($objectName);
        //return $this->objectFactory->find($objectName, $this->getProperty($objectName . 'Id'));
    }

    /**
     * 取得关联的其他对象
     *
     * @param string $objectName 对象名称
     * @return AnythingObjectInterface 返回一个实现当前接口的对象
     * @see AnythingObjectInterface
     * @author MengShaoying <mengshaoying@aliyun.com>
     */
    public function getOne(string $objectName)
    {
        return new static($objectName);
    }

    /**
     * 添加监听此对象变化的监听者
     *
     * @param object $server 监听者
     * @return void
     * @see ListenableInterface
     * @author MengShaoying <mengshaoying@aliyun.com>
     */
    public function addListener($server)
    {
        $this->listeners[] = $server;
    }

    /**
     * 通知所有监听者自身改变的事件
     *
     * @return void
     * @see ListenableInterface
     * @author MengShaoying <mengshaoying@aliyun.com>
     */
    public function notifyAll()
    {
        foreach ($this->listeners as $server) {
            $server->update();
        }
    }
}
