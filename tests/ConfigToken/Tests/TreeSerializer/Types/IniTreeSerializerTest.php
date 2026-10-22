<?php

namespace ConfigToken\Tests\TreeSerializer\Types;

use ConfigToken\TreeSerializer\Types\IniTreeSerializer;


class IniTreeSerializerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider iniDataProvider
     */
    public function testSerialize($data, $expected)
    {
        $serialized = IniTreeSerializer::serialize($data);
        $this->assertEquals($expected, $serialized);
    }

    /**
     * @dataProvider iniDataProvider
     */
    public function testDeserialize($expected, $serialized)
    {
        $data = IniTreeSerializer::deserialize($serialized);
        $this->assertEquals($expected, $data);
    }

    public function testDeserializeNamespaceException()
    {
        $this->expectException(\ConfigToken\TreeSerializer\Exception\TreeSerializerSyntaxException::class);

        $serialized = array(
            '[a]',
            'b=1',
            '',
            '[a:b]',
            'v=1',
        );
        IniTreeSerializer::deserialize(implode("\n", $serialized));
    }

    public function testDeserializeSyntaxException()
    {
        $this->expectException(\ConfigToken\TreeSerializer\Exception\TreeSerializerSyntaxException::class);

        $serialized = array(
            '{a}',
            'b=1',
        );
        IniTreeSerializer::deserialize(implode("\n", $serialized));
    }

    public function testDeserializeException()
    {
        $this->expectException(\ConfigToken\TreeSerializer\Exception\TreeSerializerSyntaxException::class);

        $serialized = array(
            'false',
        );
        IniTreeSerializer::deserialize(implode("\n", $serialized));
    }

    public function iniDataProvider()
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
                ),
                implode("\n", array(
                    'a=value of a',
                    'f=100',
                    '',
                    '[b]',
                    'a=value of ba',
                    'e=5',
                    '',
                    '[b:b]',
                    '',
                    '',
                    '[b:c]',
                    'd=value of d',
                ))
            ),
        );
    }
}