<?php

namespace ConfigToken\TreeSerializer\Types;

use ConfigToken\TreeSerializer\TreeSerializerInterface;


abstract class AbstractTreeSerializer implements TreeSerializerInterface
{
    public static function serialize($data)
    {
        throw new \Exception(
            sprintf(
                '%s->serialize() not implemented.',
                get_called_class()
            )
        );
    }

    public static function deserialize($string)
    {
        throw new \Exception(
            sprintf(
                '%s->deserialize() not implemented.',
                get_called_class()
            )
        );
    }
}