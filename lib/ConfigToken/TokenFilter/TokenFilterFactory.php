<?php

namespace ConfigToken\TokenFilter;

use ConfigToken\Exception\AlreadyRegisteredException;
use ConfigToken\TokenFilter\Exception\UnknownFilterException;
use ConfigToken\TokenFilter\Types\TokenFilterToDashes;
use ConfigToken\TokenFilter\Types\TokenFilterToDots;
use ConfigToken\TokenFilter\Types\TokenFilterToLowercase;
use ConfigToken\TokenFilter\Types\TokenFilterToUnderscore;
use ConfigToken\TokenFilter\Types\TokenFilterToUppercase;


/**
 * Factory class used to hold and register token value filters.
 */
class TokenFilterFactory
{
    /** @var TokenFilterInterface[] */
    protected static $registeredByName = array();

    protected static function registerKnownTypes()
    {
        if (!empty(static::$registeredByName)) {
            return;
        }
        static::internalRegister(new TokenFilterToDashes());
        static::internalRegister(new TokenFilterToDots());
        static::internalRegister(new TokenFilterToLowercase());
        static::internalRegister(new TokenFilterToUnderscore());
        static::internalRegister(new TokenFilterToUppercase());
    }

    /**
     * Check if the filter with the given name is registered.
     *
     * @param string $filterName The identifier of the filter.
     * @return boolean
     */
    public static function isRegisteredByName($filterName)
    {
        static::registerKnownTypes();
        return isset(static::$registeredByName[$filterName]);
    }

    /**
     * Get the filter registered with the given name.
     *
     * @param string $filterName The name of the filter.
     * @return TokenFilterInterface|null If there is no filter registered with the given name.
     * @throws UnknownFilterException
     */
    public static function getByName($filterName)
    {
        static::registerKnownTypes();
        if (isset(static::$registeredByName[$filterName])) {
            return static::$registeredByName[$filterName];
        }
        throw new UnknownFilterException($filterName);
    }

    /**
     * Used internally to register a new token filter implementation without performing checks.
     *
     * @param TokenFilterInterface $tokenFilter
     */
    protected static function internalRegister(TokenFilterInterface $tokenFilter)
    {
        static::$registeredByName[$tokenFilter::getName()] = $tokenFilter;
    }

    /**
     * Register a new filter.
     *
     * @param TokenFilterInterface $tokenFilter
     * @throws AlreadyRegisteredException
     */
    public static function register(TokenFilterInterface $tokenFilter)
    {
        if (static::isRegisteredByName($tokenFilter::getName())) {
            throw new AlreadyRegisteredException(
                sprintf(
                    'The token filter with the name %s is already registered.',
                    $tokenFilter::getName()
                )
            );
        }
        static::internalRegister($tokenFilter);
    }

    /**
     * Apply the given filter to the given value.
     *
     * @param string $filterName The name of the filter to apply.
     * @param string $value The value that needs to be filtered.
     * @param boolean $ignoreUnknownFilters If True, passing an unknown filter name will not cause an exception.
     * @param string|null $defaultValue The value returned if set to ignore unknown filter names.
     * @throws UnknownFilterException
     * @return string
     */
    public static function getFilteredValue($filterName, $value, $ignoreUnknownFilters = False, $defaultValue = null)
    {
        if (static::isRegisteredByName($filterName)) {
            $filter = static::getByName($filterName);
            return $filter::getFilteredValue($value);
        }
        if ($ignoreUnknownFilters) {
            return $defaultValue;
        }
        throw new UnknownFilterException($filterName);
    }
}