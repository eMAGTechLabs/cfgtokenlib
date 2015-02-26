<?php

namespace ConfigToken\TreeSerializer;


interface TreeSerializerInterface
{
    /**
     * @codeCoverageIgnore
     */
    public static function getContentType();
    /**
     * @codeCoverageIgnore
     */
    public static function getFileExtension();
    public static function serialize($data);
    public static function deserialize($string);
}