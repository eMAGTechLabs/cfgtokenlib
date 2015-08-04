<?php

namespace ConfigToken\TokenResolver;

use ConfigToken\Exception\AlreadyRegisteredException;
use ConfigToken\TokenResolver\Exception\UnknownTokenResolverTypeException;
use ConfigToken\TokenResolver\Types\JsonScopeTokenResolver;
use ConfigToken\TokenResolver\Types\OnDemandTokenResolver;
use ConfigToken\TokenResolver\Types\RegisteredTokenResolver;
use ConfigToken\TokenResolver\Types\ScopeTokenResolver;

class TokenResolverFactory
{
    /** @var TokenResolverInterface[] */
    protected static $registeredByType = array();

    protected static function registerKnownTypes()
    {
        if (!empty(static::$registeredByType)) {
            return;
        }
        static::internalRegister(new RegisteredTokenResolver());
        static::internalRegister(new ScopeTokenResolver());
        static::internalRegister(new JsonScopeTokenResolver());
        static::internalRegister(new OnDemandTokenResolver());
    }

    /**
     * @param string $resolverType
     * @return TokenResolverInterface
     *
     * @throws UnknownTokenResolverTypeException
     */
    public static function get($resolverType)
    {
        static::registerKnownTypes();
        if (isset(static::$registeredByType[$resolverType])) {
            return new static::$registeredByType[$resolverType];
        }
        throw new UnknownTokenResolverTypeException($resolverType);
    }

    public static function isRegistered(TokenResolverInterface $tokenResolver)
    {
        try {
            static::get($tokenResolver::getType());
            return true;
        } catch (UnknownTokenResolverTypeException $e) {
            return false;
        }
    }

    public static function isRegisteredByType($tokenResolverType)
    {
        try {
            static::get($tokenResolverType);
            return true;
        } catch (UnknownTokenResolverTypeException $e) {
            return false;
        }
    }

    /**
     * Used internally to register a new tree serializer implementation without performing checks.
     *
     * @param TokenResolverInterface $tokenResolver
     */
    protected static function internalRegister(TokenResolverInterface $tokenResolver)
    {
        static::$registeredByType[$tokenResolver::getType()] = $tokenResolver::getClassName();
    }

    public static function register(TokenResolverInterface $tokenResolver)
    {
        if (static::isRegistered($tokenResolver)) {
            throw new AlreadyRegisteredException(
                sprintf(
                    'Token resolver with type identifier "%s" is already registered.',
                    $tokenResolver::getType()
                )
            );
        }
        static::internalRegister($tokenResolver);
    }

    public static function getBaseTypeForType($resolverType)
    {
        $resolver = static::get($resolverType);
        return $resolver::getBaseType();
    }
}