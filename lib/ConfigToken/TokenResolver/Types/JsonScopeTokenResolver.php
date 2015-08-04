<?php

namespace ConfigToken\TokenResolver\Types;


use ConfigToken\TokenResolver\ScopeValueSerializers\JsonScopeTokenValueSerializer;

class JsonScopeTokenResolver extends ScopeTokenResolver
{
    public function __construct($scopeTokenName = null, $scope = null, $ignoreOutOfScope = False)
    {
        parent::__construct($scopeTokenName, $scope, $ignoreOutOfScope);
        $this->setSerializer(new JsonScopeTokenValueSerializer());
    }

    /**
     * Get the token resolver type identifier.
     *
     * @return string
     */
    public static function getType()
    {
        return 'json-scope';
    }
}