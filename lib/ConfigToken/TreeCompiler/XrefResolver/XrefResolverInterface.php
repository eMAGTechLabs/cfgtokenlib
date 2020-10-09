<?php

namespace ConfigToken\TreeCompiler\XrefResolver;

use ConfigToken\LoggerInterface;
use ConfigToken\TreeCompiler\Xref;


interface XrefResolverInterface
{
    /**
     * Get the resolver type identifier string.
     *
     * @return string
     */
    public static function getType();

    /**
     * Get the absolute location for the given Xref based on the specified include path.
     *
     * @param string $xrefLocation The location.
     * @param Xref[] $xrefPath Inclusion path.
     * @return string
     */
    public static function getAbsoluteLocation($xrefLocation, $xrefPath);

    /**
     * Return a platform specific location string derived from the given location string.
     *
     * @param $xrefLocation
     * @return mixed
     */
    public static function getPlatformSpecificLocation($xrefLocation);

    /**
     * Fetch the data from the specified location of the Xref.
     *
     * @param Xref $xref
     * @param boolean $force If true and Xref already fetched, force the resolver to fetch the data again.
     * @param LoggerInterface|null $logger
     */
    public static function resolve(Xref $xref, $force = false, LoggerInterface $logger=null, $headers=array());
}