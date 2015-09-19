<?php

namespace ConfigToken\TreeCompiler\Parsers;


class XrefTokenResolverCollectionParser
{

    protected static function validateDefinitions($definitions)
    {
        $resolverValues = array();
        $tokenResolverDefinitionIndex = 0;

        if (!is_array($definitions)) {
            throw new TokenResolverDefinitionException(
                sprintf(
                    'Token resolver definitions must be an array. (%s)',
                    json_encode($definitions)
                )
            );
        }
        foreach ($definitions as $tokenResolverKey => $tokenResolverInfo) {
        }

        return $resolverValues;
    }

    public static function parse($definitions, $tokenResolvers)
    {
        $result = new XrefTokenResolverCollection();
        // parse
        foreach ($definitions as $key => $definition) {
            $tokenResolver = XrefTokenResolverParser::parse($tokenResolverInfo);
            $result->add($xrefTokenResolver);
        }
        return $result;
    }
}