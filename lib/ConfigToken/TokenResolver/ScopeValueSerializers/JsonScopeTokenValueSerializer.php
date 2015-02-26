<?php

namespace ConfigToken\TokenResolver\ScopeValueSerializers;

use ConfigToken\TokenResolver\Exception\ScopeTokenValueSerializationException;
use ConfigToken\TokenResolver\ScopeTokenValueSerializerInterface;


class JsonScopeTokenValueSerializer implements ScopeTokenValueSerializerInterface
{
    /**
     * Serialize the given value from the scope to a string representation.
     *
     * @param array $scopeValue
     * @param boolean $escape
     * @return string
     * @throws ScopeTokenValueSerializationException
     */
    public function getSerializedValue($scopeValue, $escape = False)
    {
        $valueType = gettype($scopeValue);

        switch ($valueType) {
            case 'string':
                if ($escape) {
                    $result = json_encode($scopeValue);
                    return substr($result, 1, -1); // eliminate quotes
                } else {
                    return $scopeValue;
                }
            case 'boolean':
                return ($scopeValue ? 'true' : 'false');
            case 'integer':
                return '' . $scopeValue;
            case 'double':
                return sprintf('%.5f', $scopeValue);
            case 'array':
            case 'object':
                return json_encode($scopeValue);
            case 'NULL':
                return 'null';
            default:
                throw new ScopeTokenValueSerializationException(
                    sprintf(
                        'Unable to serialize value of type "%s".',
                        $valueType
                    )
                );
        }
    }
}