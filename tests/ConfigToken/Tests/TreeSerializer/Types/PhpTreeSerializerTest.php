<?php

namespace ConfigToken\Tests\TreeSerializer\Types;

use ConfigToken\TreeSerializer\Types\PhpTreeSerializer;


class PhpTreeSerializerTest extends \PHPUnit_Framework_TestCase
{
    const FORMAT = "<?php\n\n\$data = %s;";
    /**
     * @dataProvider phpDataProvider
     */
    public function testSerialize($data)
    {
        $serialized = PhpTreeSerializer::serialize($data);
        $expected = sprintf(self::FORMAT, var_export($data, true));
        $this->assertEquals($expected, $serialized);
    }

    /**
     * @dataProvider phpDataProvider
     */
    public function testDeserialize($data)
    {
        $serialized = sprintf(self::FORMAT, var_export($data, true));
        $deserialized = PhpTreeSerializer::deserialize($serialized);
        $this->assertEquals($data, $deserialized);
    }

    /**
     * @expectedException \ConfigToken\TreeSerializer\Exception\TreeSerializerSyntaxException
     */
    public function testDeserializeException()
    {
        $malformed = '<?php print "test"';
        PhpTreeSerializer::deserialize($malformed);
    }

    /**
     * @expectedException \ConfigToken\TreeSerializer\Exception\TreeSerializerSyntaxException
     */
    public function testDeserializeBraceException()
    {
        $malformed = '<?php }';
        PhpTreeSerializer::deserialize($malformed);
    }

    public function phpDataProvider()
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