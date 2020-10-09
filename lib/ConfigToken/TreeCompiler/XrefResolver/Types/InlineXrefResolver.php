<?php

namespace ConfigToken\TreeCompiler\XrefResolver\Types;


use ConfigToken\LoggerInterface;
use ConfigToken\TreeCompiler\Xref;

class InlineXrefResolver extends AbstractXrefResolver
{
    /**
     * Get the resolver type identifier string.
     *
     * @return string
     */
    public static function getType()
    {
        return 'inline';
    }

    /**
     * Fetch the data from the specified location of the Xref.
     *
     * @param Xref $xref
     * @param boolean $force If true and Xref already fetched, force the resolver to fetch the data again.
     * @param LoggerInterface|null $logger
     * @param array $headers
     * @throws \Exception
     */
    public static function resolve(Xref $xref, $force = false, LoggerInterface $logger=null, $headers=array())
    {
        $xref->setResolved(true);
    }
}