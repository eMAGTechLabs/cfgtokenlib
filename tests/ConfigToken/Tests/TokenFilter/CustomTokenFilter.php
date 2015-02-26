<?php

namespace ConfigToken\Tests\TokenFilter;

use ConfigToken\TokenFilter\TokenFilterInterface;


class CustomTokenFilter implements TokenFilterInterface
{
    /**
     * The name of the filter as it will be used in the token.
     *
     * @return string
     */
    public static function getName()
    {
        return 'custom_test_filter';
    }

    /**
     * Apply the filter to the given value.
     *
     * @param string $value The value that needs to be filtered.
     * @return string
     */
    public static function getFilteredValue($value)
    {
        return str_replace('.', '->', $value);
    }

}