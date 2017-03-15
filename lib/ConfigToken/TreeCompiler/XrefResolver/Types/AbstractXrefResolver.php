<?php

namespace ConfigToken\TreeCompiler\XrefResolver\Types;

use ConfigToken\LoggerInterface;
use ConfigToken\TreeCompiler\XrefResolver\Exception\InvalidXrefTypeException;
use ConfigToken\TreeCompiler\Xref;
use ConfigToken\TreeCompiler\XrefResolver\XrefResolverInterface;


abstract class AbstractXrefResolver implements XrefResolverInterface
{
    /**
     * Get the absolute location for the given Xref based on the specified include path.
     *
     * @param string $xrefLocation The location.
     * @param Xref[] $xrefPath Inclusion path.
     * @return string
     */
    public static function getAbsoluteLocation($xrefLocation, $xrefPath)
    {
        return $xrefLocation;
    }

    /**
     * Return a platform specific location string derived from the given location string.
     *
     * @param $xrefLocation
     * @return mixed
     */
    public static function getPlatformSpecificLocation($xrefLocation)
    {
        return $xrefLocation;
    }

    /**
     * Fetch the data from the specified location of the Xref.
     *
     * @param Xref $xref
     * @param boolean $force If true and Xref already fetched, force the resolver to fetch the data again.
     * @param LoggerInterface|null $logger
     * @throws \Exception
     */
    public static function resolve(Xref $xref, $force = false, LoggerInterface $logger=null)
    {
        throw new \Exception(
            sprintf(
                '%s->resolve() not implemented.',
                get_called_class()
            )
        );
    }

    /**
     * Used internally to verify if this Xref has the same type as the given Xref.
     *
     * @param Xref $xref The Xref to compare to.
     * @throws InvalidXrefTypeException
     */
    protected static function matchType(Xref $xref)
    {
        if ($xref->getType() !== static::getType()) {
            throw new InvalidXrefTypeException(
                sprintf(
                    '%s is unable to resolve Xrefs of type %s.',
                    get_called_class(),
                    $xref->getType()
                )
            );
        }
    }
}