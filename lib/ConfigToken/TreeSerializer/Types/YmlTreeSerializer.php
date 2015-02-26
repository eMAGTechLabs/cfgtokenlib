<?php

namespace ConfigToken\TreeSerializer\Types;


class YmlTreeSerializer extends AbstractTreeSerializer
{
    /**
     * @codeCoverageIgnore
     * @return string
     */
    public static function getContentType()
    {
        return 'application/x-yaml';
    }

    /**
     * @codeCoverageIgnore
     * @return string
     */
    public static function getFileExtension()
    {
        return 'yml';
    }
}