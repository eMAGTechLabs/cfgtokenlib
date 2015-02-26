<?php

namespace ConfigToken\Tests\TokenResolver;

use ConfigToken\TokenResolver\ScopeTokenValueSerializerInterface;


class CustomScopeTokenValueSerializer implements ScopeTokenValueSerializerInterface
{
    /**
     * Serialize the given value from the scope to a string representation.
     *
     * @param array $scopeValue
     * @param boolean $escape
     * @return string
     */
    public function getSerializedValue($scopeValue, $escape = False)
    {
        return $scopeValue;
    }

}