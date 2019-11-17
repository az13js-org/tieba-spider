<?php
namespace Solution\AnythingObject\Abstracts;

/**
 * 对象结构
 *
 * @author MengShaoying <mengshaoying@aliyun.com>
 */
abstract class AbstractAnythingObjectStruct
{
    /**
     * 设置对象名称
     *
     * @param string $name
     * @return void
     * @author MengShaoying <mengshaoying@aliyun.com>
     */
    abstract public function setObjectName(string $name);

    /**
     * 获取对象名称
     *
     * @return string 对象名
     * @author MengShaoying <mengshaoying@aliyun.com>
     */
    abstract public function getObjectName(): string;

    /**
     * 返回当前的结构
     *
     * @return array
     * @author MengShaoying <mengshaoying@aliyun.com>
     */
    abstract public function getStruct(): array;

    /**
     * 添加属性
     *
     * @param string $propertyName 属性名称
     * @return bool 添加成功返回true,失败返回false。属性存在时返回false
     * @author MengShaoying <mengshaoying@aliyun.com>
     */
    abstract public function addProperty(string $propertyName): bool;
}
