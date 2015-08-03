<?php

namespace ConfigToken\TreeCompiler\XrefResolver\Types;

use ConfigToken\TreeCompiler\XrefResolver\Exception\UnknownXrefTypeException;
use ConfigToken\TreeCompiler\XrefResolver\Exception\XrefResolverFetchException;
use ConfigToken\TreeCompiler\Xref;
use ConfigToken\TreeSerializer\TreeSerializerFactory;


class UrlXrefResolver extends AbstractXrefResolver
{
    public static function getType()
    {
        return 'url';
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
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $xref->getLocation());
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        $data = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode != 200) {
            throw new XrefResolverFetchException($xref, sprintf('Got response code %d', $httpCode));
        }
        if (TreeSerializerFactory::isRegisteredByContentType($contentType)) {
            $serializer = TreeSerializerFactory::getByContentType($contentType);
            $xref->setContentType($contentType);
        } else {
            $path = parse_url($xref->getLocation(), PHP_URL_PATH);
            $fileExtension = pathinfo($path, PATHINFO_EXTENSION);
            if (!TreeSerializerFactory::isRegisteredByFileExtension($fileExtension)) {
                throw new UnknownXrefTypeException(
                    'Unable to find resolver for Xref content type "%s" or file extension "%s" for location "%s".',
                    $contentType,
                    $fileExtension,
                    $xref->getLocation()
                );
            }
            $serializer = TreeSerializerFactory::getByFileExtension($fileExtension);
            $xref->setContentType($serializer::getContentType());
        }
        $data = $serializer::deserialize($data);
        $xref->setData($data);
        $xref->setResolved(true);
    }
}