<?php

namespace ConfigToken\Tests\TreeSerializer\Types;

use ConfigToken\TreeSerializer\Types\JsonTreeSerializer;


class JsonTreeSerializerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider jsonDataProvider
     */
    public function testSerialize($data)
    {
        $serialized = JsonTreeSerializer::serialize($data);
        $this->assertEquals(json_encode($data, JSON_PRETTY_PRINT), $serialized);
    }

    /**
     * @dataProvider jsonDataProvider
     */
    public function testDeserialize($data)
    {
        $deserialized = JsonTreeSerializer::deserialize(json_encode($data));
        $this->assertEquals($data, $deserialized);
    }

    /**
     * @expectedException \ConfigToken\TreeSerializer\Exception\TreeSerializerSyntaxException
     */
    public function testDeserializeException()
    {
        $malformed = '{"a":[0,1,2,],}';
        JsonTreeSerializer::deserialize($malformed);
    }

    public function jsonDataProvider()
    {
        return array(
            array(
                array(
                    'a' => 'value of a',
                    'b' => array(
                        'a' => 'value of ba',
                        'b' => array(
                        ),
                        'c' => array(
                            'd' => 'value of d'
                        ),
                        'e' => 5
                    ),
                    'f' => '100',
                )
            ),
        );
    }
}