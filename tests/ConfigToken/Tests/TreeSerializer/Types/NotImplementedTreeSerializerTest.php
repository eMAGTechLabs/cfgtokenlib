<?php

namespace ConfigToken\Tests\TreeSerializer\Types;

use ConfigToken\TreeSerializer\Types\XmlTreeSerializer;
use ConfigToken\TreeSerializer\Types\YmlTreeSerializer;


class NotImplementedTreeSerializerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Exception
     */
    public function testXmlSerializeNotImplementedException()
    {
        XmlTreeSerializer::serialize(array());
    }

    /**
     * @expectedException \Exception
     */
    public function testXmlDeserializeNotImplementedException()
    {
        XmlTreeSerializer::deserialize('');
    }

    /**
     * @expectedException \Exception
     */
    public function testYmlSerializeNotImplementedException()
    {
        YmlTreeSerializer::serialize(array());
    }

    /**
     * @expectedException \Exception
     */
    public function testYmlDeserializeNotImplementedException()
    {
        YmlTreeSerializer::deserialize('');
    }
}