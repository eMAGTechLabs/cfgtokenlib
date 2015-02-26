<?php

namespace ConfigToken\TokenResolver\Types;


use ConfigToken\TokenResolver\ScopeValueSerializers\JsonScopeTokenValueSerializer;

class JsonScopeTokenResolver extends ScopeTokenResolver
{
    function __construct($scopeTokenName, $scope = null, $ignoreOutOfScope = False)
    {
        parent::__construct($scopeTokenName, $scope, $ignoreOutOfScope);
        $this->setSerializer(new JsonScopeTokenValueSerializer());
    }
}