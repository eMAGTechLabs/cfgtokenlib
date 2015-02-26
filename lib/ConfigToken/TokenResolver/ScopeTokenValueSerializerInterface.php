<?php

namespace ConfigToken\TokenResolver;


interface ScopeTokenValueSerializerInterface
{
    /**
     * Serialize the given value from the scope to a string representation.
     *
     * @param array $scopeValue
     * @param boolean $escape
     * @return string
     */
    public function getSerializedValue($scopeValue, $escape = False);
}