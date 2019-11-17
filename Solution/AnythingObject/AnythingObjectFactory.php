<?php
namespace Solution\AnythingObject;

use Solution\AnythingObject\Interfaces\FactoryInterface;

/**
 * 生产 AnythingObject 的工厂
 *
 * @see FactoryInterface
 * @author MengShaoying <mengshaoying@aliyun.com>
 */
class AnythingObjectFactory implements FactoryInterface
{
    /** @var int 产品号 */
    private $productId = 0;

    /**
     * 创建新的 AnythingObject
     *
     * @param string $objectName 对象名称
     * @return AnythingObject
     * @author MengShaoying <mengshaoying@aliyun.com>
     */
    public function create(string $objectName): AnythingObject
    {
        // 创建 AnythingObject 对象
        $obj = new AnythingObject();
        $obj->setObjectName($objectName);
        $obj->setProperty('id', ++$this->productId);
        $obj->bindObjectFactory($this);
        // 绑定到观察者上
        $server = AnythingObjectStruct::getInstance($objectName);
        $server->bindObject($obj);
        return $obj;
    }
}
