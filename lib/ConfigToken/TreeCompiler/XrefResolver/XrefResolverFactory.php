<?php

namespace ConfigToken\TreeCompiler\XrefResolver;

use ConfigToken\Exception\AlreadyRegisteredException;
use ConfigToken\TreeCompiler\XrefResolver\Types\UrlXrefResolver;
use ConfigToken\TreeCompiler\XrefResolver\Types\LocalFileXrefResolver;
use ConfigToken\TreeCompiler\XrefResolver\Exception\UnknownXrefTypeException;


class XrefResolverFactory
{
    /** @var XrefResolverInterface[] */
    protected static $registeredByType = array();

    protected static function registerKnownTypes()
    {
        if (!empty(static::$registeredByType)) {
            return;
        }
        static::internalRegister(new UrlXrefResolver());
        static::internalRegister(new LocalFileXrefResolver());
    }

    /**
     * @param string $xrefType
     * @return XrefResolverInterface
     *
     * @throws UnknownXrefTypeException
     */
    public static function getByType($xrefType)
    {
        static::registerKnownTypes();
        if (isset(static::$registeredByType[$xrefType])) {
            return static::$registeredByType[$xrefType];
        }
        throw new UnknownXrefTypeException($xrefType);
    }

    public static function isRegistered(XrefResolverInterface $xrefResolver)
    {
        return (isset(static::$registeredByType[$xrefResolver::getType()]));
    }

    protected static function internalRegister(XrefResolverInterface $xrefResolver)
    {
        static::$registeredByType[$xrefResolver::getType()] = $xrefResolver;
    }

    public static function register(XrefResolverInterface $xrefResolver)
    {
        if (static::isRegistered($xrefResolver)) {
            throw new AlreadyRegisteredException(
                sprintf(
                    'Xref resolver for Xref type %s is already registered.',
                    $xrefResolver::getType()
                )
            );
        }
        static::internalRegister($xrefResolver);
    }
}