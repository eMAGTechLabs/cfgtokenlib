<?php

namespace ConfigToken\TreeCompiler\XrefResolver\Types;

use ConfigToken\LoggerInterface;
use ConfigToken\TreeCompiler\XrefResolver\Exception\XrefResolverFetchException;
use ConfigToken\TreeCompiler\Xref;
use ConfigToken\TreeSerializer\Exception\TreeSerializerSyntaxException;
use ConfigToken\TreeSerializer\TreeSerializerFactory;


class LocalFileXrefResolver extends AbstractXrefResolver
{
    /**
     * Get the resolver type identifier string.
     *
     * @return string
     */
    public static function getType()
    {
        return 'file';
    }

    /**
     * Get the absolute location for the given Xref based on the specified include path.
     *
     * @param string $xrefLocation The location.
     * @param Xref[] $xrefPath Inclusion path.
     * @return string
     */
    public static function getAbsoluteLocation($xrefLocation, $xrefPath)
    {
        $xrefLocation = static::getPlatformSpecificLocation($xrefLocation);
        // no inclusion path, return absolute location or as set
        if ((!isset($xrefPath)) || (!is_array($xrefPath)) || (count($xrefPath) == 0)) {
            $dirname = pathinfo($xrefLocation, PATHINFO_DIRNAME);
            if ($dirname == '.') {
                return getcwd() . DIRECTORY_SEPARATOR . $xrefLocation;
            }
            return $xrefLocation;
        }

        $xrefLocationLen = strlen($xrefLocation);

        // empty location
        if ($xrefLocationLen == 0) {
            return $xrefLocation;
        }

        // absolute location
        if (($xrefLocation[0] == DIRECTORY_SEPARATOR) || (($xrefLocationLen >= 2) && ($xrefLocation[1] == ':'))) {
            return $xrefLocation;
        }

        // relative location
        while (count($xrefPath) > 0) {
            $prevXref = array_pop($xrefPath);
            if (!($prevXref instanceof Xref) || ($prevXref->getType() != static::getType())) {
                continue;
            }
            $prevXrefLocation = static::getAbsoluteLocation($prevXref->getLocation(), $xrefPath);
            $prevXrefPath = pathinfo($prevXrefLocation, PATHINFO_DIRNAME);
            if ($prevXrefPath == '.') {
                $prevXrefPath = null;
                break;
            }
            if (strlen($prevXrefPath) == 0) {
                $prevXrefPath = null;
                continue;
            }
            break;
        }

        if (!isset($prevXrefPath)) {
            $prevXrefPath = getcwd();
        }

        $absoluteXrefLocation = $prevXrefPath . DIRECTORY_SEPARATOR . $xrefLocation;
        $realXrefLocation = realpath($absoluteXrefLocation);

        return $realXrefLocation === false ? $absoluteXrefLocation : $realXrefLocation;
    }

    /**
     * Return a platform specific location string derived from the given location string.
     *
     * @param $xrefLocation
     * @return mixed
     */
    public static function getPlatformSpecificLocation($xrefLocation)
    {
        return str_replace(array('\\', '/'), DIRECTORY_SEPARATOR, $xrefLocation);
    }

    /**
     * Fetch the data from the specified location of the Xref.
     *
     * @param Xref $xref
     * @param boolean $force If true and Xref already fetched, force the resolver to fetch the data again.
     * @param array $headers
     * @param LoggerInterface|null $logger
     * @throws XrefResolverFetchException
     */
    public static function resolve(Xref $xref, $force = false, LoggerInterface $logger=null, $headers=array())
    {
        if ($xref->isResolved() && (!$force)) {
            return;
        }
        static::matchType($xref);
        if (!$xref->hasLocation()) {
            throw new XrefResolverFetchException($xref);
        }
        $xrefLocation = $xref->getLocation();
        if (!file_exists($xrefLocation)) {
            throw new XrefResolverFetchException($xref, 'File does not exist.');
        }
        try {
            $data = file_get_contents($xrefLocation);
        } catch (\Exception $e) {
            throw new XrefResolverFetchException($xref, $e->getMessage());
        }
        $xref->setResolved(false);
        if ($xref->hasContentType()) {
            $serializer = TreeSerializerFactory::getByContentType($xref->getContentType());
        } else {
            $fileExtension = pathinfo($xrefLocation, PATHINFO_EXTENSION);
            $serializer = TreeSerializerFactory::getByFileExtension($fileExtension);
            $xref->setContentType($serializer::getContentType());
        }
        try {
            $data = $serializer::deserialize($data);
        } catch (TreeSerializerSyntaxException $e) {
            throw new XrefResolverFetchException($xref, $e->getMessage());
        }
        $xref->setData($data);
        $xref->setResolved(true);
    }
}