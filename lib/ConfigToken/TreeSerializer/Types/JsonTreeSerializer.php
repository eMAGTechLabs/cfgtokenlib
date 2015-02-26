<?php

namespace ConfigToken\TreeSerializer\Types;

use ConfigToken\TreeSerializer\Exception\TreeSerializerSyntaxException;


class JsonTreeSerializer extends AbstractTreeSerializer
{
    /**
     * @codeCoverageIgnore
     * @return string
     */
    public static function getContentType()
    {
        return 'application/json';
    }

    /**
     * @codeCoverageIgnore
     * @return string
     */
    public static function getFileExtension()
    {
        return 'json';
    }

    public static function serialize($data)
    {
        return json_encode($data, JSON_PRETTY_PRINT);
    }

    public static function deserialize($string)
    {
        $data = json_decode($string, true);
        if ((!isset($data)) && (json_last_error() != JSON_ERROR_NONE)) {
            throw new TreeSerializerSyntaxException(
                sprintf(
                    'Unable to deserialize json tree: %s',
                    json_last_error_msg()
                )
            );
        }
        return $data;
    }
}