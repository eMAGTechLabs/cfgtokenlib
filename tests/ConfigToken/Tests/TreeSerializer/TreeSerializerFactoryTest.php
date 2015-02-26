<?php

namespace ConfigToken\Tests\TreeSerializer;

use ConfigToken\Tests\TreeSerializer\Types\ValidCustomTreeSerializer;
use ConfigToken\TreeSerializer\TreeSerializerFactory;


class TreeSerializerFactoryTest extends \PHPUnit_Framework_TestCase
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

    /**
     * @expectedException \ConfigToken\Exception\AlreadyRegisteredException
     */
    public function testAlreadyRegistered()
    {
        $serializer = new ValidCustomTreeSerializer();
        TreeSerializerFactory::register($serializer);
        TreeSerializerFactory::register($serializer);
    }

    /**
     * @expectedException \ConfigToken\TreeSerializer\Exception\UnknownContentTypeException
     */
    public function testUnknownContentType()
    {
        TreeSerializerFactory::getByContentType('rubbish');
    }

    /**
     * @expectedException \ConfigToken\TreeSerializer\Exception\UnknownFileExtensionException
     */
    public function testUnknownFileExtension()
    {
        TreeSerializerFactory::getByFileExtension('rubbish');
    }
}