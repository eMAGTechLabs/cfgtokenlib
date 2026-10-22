<?php

namespace ConfigToken\Tests\TreeSerializer\Types;

use ConfigToken\TreeSerializer\Types\XmlTreeSerializer;
use ConfigToken\TreeSerializer\Types\YmlTreeSerializer;


class NotImplementedTreeSerializerTest extends \PHPUnit\Framework\TestCase
{

    public function testXmlSerializeNotImplementedException()
    {
        $this->expectException(\Exception::class);

        XmlTreeSerializer::serialize(array());
    }

    public function testXmlDeserializeNotImplementedException()
    {
        $this->expectException(\Exception::class);

        XmlTreeSerializer::deserialize('');
    }

    public function testYmlSerializeNotImplementedException()
    {
        $this->expectException(\Exception::class);

        YmlTreeSerializer::serialize(array());
    }

    public function testYmlDeserializeNotImplementedException()
    {
        $this->expectException(\Exception::class);

        YmlTreeSerializer::deserialize('');
    }
}