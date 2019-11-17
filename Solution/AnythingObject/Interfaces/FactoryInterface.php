<?php
namespace Solution\AnythingObject\Interfaces;

/**
 * 工厂方法模式接口
 *
 * 此接口和 AnythingObjectInterface 接口一起组成工厂方法模式所需的两个接口。
 *
 * @see AnythingObjectInterface 产品接口
 * @author MengShaoying <mengshaoying@aliyun.com>
 */
interface FactoryInterface
{
    /**
     * 工厂方法
     *
     * 返回实现了 AnythingObjectInterface 接口的具体对象
     *
     * @return AnythingObjectInterface
     * @see AnythingObjectInterface
     * @author MengShaoying <mengshaoying@aliyun.com>
     */
    public function create(string $objectName);
}
