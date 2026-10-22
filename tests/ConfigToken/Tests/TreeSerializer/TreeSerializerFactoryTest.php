<?php

namespace ConfigToken\Tests\TreeSerializer;

use ConfigToken\Tests\TreeSerializer\Types\ValidCustomTreeSerializer;
use ConfigToken\TreeSerializer\TreeSerializerFactory;


class TreeSerializerFactoryTest extends \PHPUnit\Framework\TestCase
{
    public function testRegister()
    {
        $serializer = new ValidCustomTreeSerializer();
        TreeSerializerFactory::register($serializer);
        $this->assertTrue(TreeSerializerFactory::isRegistered($serializer));
        $this->assertTrue(TreeSerializerFactory::isRegisteredByFileExtension($serializer->getFileExtension()));
        $this->assertTrue(TreeSerializerFactory::isRegisteredByContentType($serializer->getContentType()));
        $this->assertEquals($serializer, TreeSerializerFactory::getByFileExtension($serializer->getFileExtension()));
        $this->assertEquals($serializer, TreeSerializerFactory::getByContentType($serializer->getContentType()));
    }

    public function testAlreadyRegistered()
    {
        $this->expectException(\ConfigToken\Exception\AlreadyRegisteredException::class);

        $serializer = new ValidCustomTreeSerializer();
        TreeSerializerFactory::register($serializer);
        TreeSerializerFactory::register($serializer);
    }

    public function testUnknownContentType()
    {
        $this->expectException(\ConfigToken\TreeSerializer\Exception\UnknownContentTypeException::class);

        TreeSerializerFactory::getByContentType('rubbish');
    }

    public function testUnknownFileExtension()
    {
        $this->expectException(\ConfigToken\TreeSerializer\Exception\UnknownFileExtensionException::class);

        TreeSerializerFactory::getByFileExtension('rubbish');
    }
}