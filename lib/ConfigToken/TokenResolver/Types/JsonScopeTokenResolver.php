<?php

namespace ConfigToken\TokenResolver\Types;


use ConfigToken\TokenResolver\ScopeValueSerializers\JsonScopeTokenValueSerializer;

/**
 * Scope token resolver that serializes the values to JSON format.
 *
 * @package ConfigToken\TokenResolver\Types
 */
class JsonScopeTokenResolver extends ScopeTokenResolver
{
    const TYPE = 'json-scope';

    /**
     * Initialize the token resolver with the given options.
     *
     * @param string|null $scopeTokenName The scope token name. Null for default (none).
     * @param array|null $scope The array of registered scope values. Null for default (empty).
     * @param array|null $options Associative array of option values.
     * @param string|null $ignoreOutOfScope The ignore out of scope flag value. Null for default (false).
     */
    public function __construct($scopeTokenName = null, array $scope = null, array $options = null,
                                $ignoreOutOfScope = null)
    {
        parent::__construct($scopeTokenName, $scope, $options, $ignoreOutOfScope);
        $this->setSerializer(new JsonScopeTokenValueSerializer());
    }

    /**
     * Get the token resolver type identifier.
     *
     * @return string
     */
    public static function getType()
    {
        return static::TYPE;
    }
}