<?php

namespace ConfigToken\TokenFilter\Types;

use ConfigToken\TokenFilter\TokenFilterInterface;


class TokenFilterToUnderscore implements TokenFilterInterface
{
    /**
     * The name of the filter as it will be used in the token.
     *
     * @return string
     */
    public static function getName()
    {
        return 'underscore';
    }

    /**
     * Apply the filter to the given value.
     *
     * @param string $value The value that needs to be filtered.
     * @return string
     */
    public static function getFilteredValue($value)
    {
        return str_replace(array('-', '.', ' '), '_', $value);
    }
}