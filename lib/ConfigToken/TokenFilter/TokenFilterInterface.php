<?php

namespace ConfigToken\TokenFilter;


/**
 * Interface to create custom token value filters.
 */
interface TokenFilterInterface
{
    /**
     * The name of the filter as it will be used in the token.
     *
     * @return string
     */
    public static function getName();

    /**
     * Apply the filter to the given value.
     *
     * @param string $value The value that needs to be filtered.
     * @return string
     */
    public static function getFilteredValue($value);
}