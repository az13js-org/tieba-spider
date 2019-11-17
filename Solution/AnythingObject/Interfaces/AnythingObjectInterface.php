<?php
namespace Solution\AnythingObject\Interfaces;

/**
 * 工厂方法模式接口
 *
 * 产品接口。
 * 此接口和 FactoryInterface 接口一起组成工厂方法模式所需的两个接口。
 *
 * @see FactoryInterface 工厂接口
 * @author MengShaoying <mengshaoying@aliyun.com>
 */
interface AnythingObjectInterface
{
    /**
     * 设置对象名称
     *
     * @param string $name
     * @return mixed
     * @author MengShaoying <mengshaoying@aliyun.com>
     */
    public function setObjectName(string $name);

    /**
     * 获取对象名称
     *
     * @return string 对象名
     * @author MengShaoying <mengshaoying@aliyun.com>
     */
    public function getObjectName(): string;

    /**
     * 绑定一个用来生成任意对象（实现了 FactoryInterface 接口的具体工厂）的工厂对象
     *
     * @param FactoryInterface $objectFactory
     * @return mixed
     * @author MengShaoying <mengshaoying@aliyun.com>
     */
    public function bindObjectFactory($objectFactory);

    /**
     * 获取某个属性
     *
     * @param string $propertyName 属性名称
     * @return string
     * @author MengShaoying <mengshaoying@aliyun.com>
     */
    public function getProperty(string $propertyName): string;

    /**
     * 获取当前存在的所有属性
     *
     * @return string[]
     * @author MengShaoying <mengshaoying@aliyun.com>
     */
    public function getPropertiesExists(): array;

    /**
     * 设置某个属性
     *
     * @param string $propertyName 属性名称
     * @param string $val 所设置的属性的值
     * @return mixed
     * @author MengShaoying <mengshaoying@aliyun.com>
     */
    public function setProperty(string $propertyName, string $val);

    /**
     * 获取多个其他产品对象
     *
     * @param string $objectName 对象名称
     * @return array
     * @author MengShaoying <mengshaoying@aliyun.com>
     */
    public function getMany(string $objectName): array;

    /**
     * 获取父对象
     *
     * 上层对象可以用 getMany 取得包含自己的数组，此方法反过来获取上层对像
     *
     * @param string $objectName 父对象名称
     * @return AnythingObjectInterface 返回一个实现当前接口的对象
     * @author MengShaoying <mengshaoying@aliyun.com>
     */
    public function getParent(string $objectName);

    /**
     * 取得关联的其他对象
     *
     * @param string $objectName 对象名称
     * @return AnythingObjectInterface 返回一个实现当前接口的对象
     * @author MengShaoying <mengshaoying@aliyun.com>
     */
    public function getOne(string $objectName);
}
