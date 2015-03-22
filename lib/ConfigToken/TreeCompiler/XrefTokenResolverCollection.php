<?php

namespace ConfigToken\TreeCompiler;


use ConfigToken\TokenParser;
use ConfigToken\TokenResolver\Types\RegisteredTokenResolver;
use ConfigToken\TokenResolver\Types\ScopeTokenResolver;

class XrefTokenResolverCollection
{
    /** @var XrefTokenResolver[] */
    protected $collection;

    public function add(XrefTokenResolver $xrefTokenResolver)
    {
        $this->collection[] = $xrefTokenResolver;
    }

    protected function resolveAndInjectArray(&$array, XrefTokenResolver $xrefTokenResolver)
    {
        if (!$xrefTokenResolver->hasTokenResolver()) {
            throw new \Exception('No token resolver found for Xref token resolver.');
        }
        $tokenResolver = $xrefTokenResolver->getTokenResolver();
        $hasValues = False;
        if ($tokenResolver instanceof RegisteredTokenResolver) {
            $hasValues = $tokenResolver->hasRegisteredTokenValues();
        }
        if ($tokenResolver instanceof ScopeTokenResolver) {
            $hasValues = $tokenResolver->hasScope();
        }
        if (!$hasValues) {
            if ($xrefTokenResolver->hasXref()) {
                $xref = $xrefTokenResolver->getXref();
                $xref->resolve();
                $values = $xref->getData();
            } else {
                $values = $xrefTokenResolver->getRegisteredTokenValues();
            }
            if ($tokenResolver instanceof RegisteredTokenResolver) {
                $tokenResolver->setRegisteredTokenValues($values);
            }
            if ($tokenResolver instanceof ScopeTokenResolver) {
                $tokenResolver->setScope($values);
            }
        }
        $tokenParser = new TokenParser();

    }

    public function applyToArray(&$array)
    {
        foreach ($this->collection as $xrefTokenResolver) {
            $this->resolveAndInjectArray($array, $xrefTokenResolver);
        }
    }
}