<?php

namespace ConfigToken\TreeSerializer\Types;


class XmlTreeSerializer extends AbstractTreeSerializer
{
    /**
     * @codeCoverageIgnore
     * @return string
     */
    public static function getContentType()
    {
        return 'application/xml';
    }

    /**
     * @codeCoverageIgnore
     * @return string
     */
    public static function getFileExtension()
    {
        return 'xml';
    }
}