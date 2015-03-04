<?php

namespace ConfigToken\TreeCompiler\XrefResolver\Types;

use ConfigToken\TreeCompiler\XrefResolver\Exception\XrefResolverFetchException;
use ConfigToken\TreeCompiler\Xref;
use ConfigToken\TreeSerializer\TreeSerializerFactory;


class LocalFileXrefResolver extends AbstractXrefResolver
{
    public static function getType()
    {
        return 'file';
    }

    public static function resolve(Xref $xref, $force = false)
    {
        if ($xref->isResolved() && (!$force)) {
            return;
        }
        static::matchType($xref);
        if (!$xref->hasLocation()) {
            throw new XrefResolverFetchException($xref);
        }
        if (!file_exists($xref->getLocation())) {
            throw new XrefResolverFetchException($xref, 'File does not exist.');
        }
        try {
            $data = file_get_contents($xref->getLocation());
        } catch (\Exception $e) {
            throw new XrefResolverFetchException($xref, $e->getMessage());
        }
        $xref->setResolved(false);
        if ($xref->hasContentType()) {
            $serializer = TreeSerializerFactory::getByContentType($xref->getContentType());
        } else {
            $fileExtension = pathinfo($xref->getLocation(), PATHINFO_EXTENSION);
            $serializer = TreeSerializerFactory::getByFileExtension($fileExtension);
            $xref->setContentType($serializer::getContentType());
        }
        $data = $serializer::deserialize($data);
        $xref->setData($data);
        $xref->setResolved(true);
    }
}