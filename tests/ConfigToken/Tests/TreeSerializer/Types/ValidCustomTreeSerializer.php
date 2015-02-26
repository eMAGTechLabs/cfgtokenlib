<?php

namespace ConfigToken\Tests\TreeSerializer\Types;

use ConfigToken\TreeSerializer\TreeSerializerInterface;


class ValidCustomTreeSerializer implements TreeSerializerInterface
{
    /**
     * @codeCoverageIgnore
     */
    public static function getContentType()
    {
        return 'content-type';
    }

    /**
     * @codeCoverageIgnore
     */
    public static function getFileExtension()
    {
        return 'ext';
    }

    public static function serialize($data)
    {
        return 'ok';
    }

    public static function deserialize($string)
    {
        return 'ok';
    }

}