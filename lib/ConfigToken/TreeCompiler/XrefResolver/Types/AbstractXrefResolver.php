<?php

namespace ConfigToken\TreeCompiler\XrefResolver\Types;

use ConfigToken\TreeCompiler\XrefResolver\Exception\InvalidXrefTypeException;
use ConfigToken\TreeCompiler\Xref;
use ConfigToken\TreeCompiler\XrefResolver\XrefResolverInterface;


abstract class AbstractXrefResolver implements XrefResolverInterface
{
    public static function resolve(Xref $xref)
    {
        throw new \Exception(
            sprintf(
                '%s->resolve() not implemented.',
                get_called_class()
            )
        );
    }

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