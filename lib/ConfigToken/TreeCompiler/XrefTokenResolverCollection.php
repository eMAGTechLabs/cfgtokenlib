<?php

namespace ConfigToken\TreeCompiler;


use ConfigToken\TokenCollection;
use ConfigToken\TokenInjector;
use ConfigToken\TokenParser;
use ConfigToken\TokenResolver\Types\RegisteredTokenResolver;
use ConfigToken\TokenResolver\Types\ScopeTokenResolver;
use ConfigToken\TreeCompiler;

class XrefTokenResolverCollection
{
    /** @var XrefTokenResolver[] */
    protected $collection = array();

    protected static $_KEY = 0;
    protected static $_KEY_TOKENS = 1;
    protected static $_KEY_REF = 2;
    protected static $_VALUE = 3;
    protected static $_VALUE_TOKENS = 4;
    protected static $_VALUE_REF = 5;
    protected static $_LEVEL = 6;

    public function add(XrefTokenResolver $xrefTokenResolver)
    {
        $this->collection[] = $xrefTokenResolver;
    }

    public function addCollection(XrefTokenResolverCollection $xrefTokenResolverCollection)
    {
        $this->collection = array_merge($this->collection, $xrefTokenResolverCollection->collection);
    }

    public function prepend(XrefTokenResolver $xrefTokenResolver)
    {
        array_unshift($this->collection, $xrefTokenResolver);
    }

    public function prependCollection(XrefTokenResolverCollection $xrefTokenResolverCollection)
    {
        $this->collection = array_merge($xrefTokenResolverCollection->collection, $this->collection);
    }

    protected function resolveTokens(TokenCollection $tokens, XrefTokenResolver $xrefTokenResolver)
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
                if (!$xref->isResolved()) {
                    $treeCompiler = new TreeCompiler();
                    $compiledValues = $treeCompiler->compileXref($xref);
                    $xref->setData($compiledValues);
                }
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
        $xrefTokenResolver->resolve($tokens);
    }

    protected function getTokensFromArray(&$array, TokenParser $parser, &$result, $level=0)
    {
        foreach ($array as $key => &$value) {
            $t = array();
            if (gettype($key) == 'string') {
                $tokens = $parser->parseString($key);
                if (!$tokens->isEmpty()) {
                    $t[self::$_KEY] = $key;
                    $t[self::$_KEY_TOKENS] = $tokens;
                    $t[self::$_KEY_REF] = &$array;
                }
            }
            $valueType = gettype($value);
            if ($valueType == 'string') {
                $tokens = $parser->parseString($value);
                if (!$tokens->isEmpty()) {
                    $t[self::$_VALUE] = $value;
                    $t[self::$_VALUE_TOKENS] = $tokens;
                    $t[self::$_VALUE_REF] = &$value;
                }
            }
            if (count($t) > 0) {
                $t[self::$_LEVEL] = $level;
                $result[] = $t;
                unset($t);
            }
            if ($valueType == 'array') {
                $this->getTokensFromArray($value, $parser, $result, $level + 1);
            }
        }
        unset($value);
        if ($level == 0) {
            usort($result, function($a, $b) {
               return $b[XrefTokenResolverCollection::$_LEVEL] - $a[XrefTokenResolverCollection::$_LEVEL];
            });
        }
    }

    public function applyToString($string)
    {
        foreach ($this->collection as $xrefTokenResolver) {
            $tokenParser = $xrefTokenResolver->getTokenParser();
            $tokens = $tokenParser->parseString($string);
            $this->resolveTokens($tokens, $xrefTokenResolver);
            $string = TokenInjector::injectString($string, $tokens);
        }
        return $string;
    }

    public function applyToArray(&$array)
    {
        foreach ($this->collection as $xrefTokenResolver) {
            $tokenParser = $xrefTokenResolver->getTokenParser();
            $lookup = array();
            $this->getTokensFromArray($array, $tokenParser, $lookup);
            foreach ($lookup as $record) {
                if (isset($record[self::$_VALUE_TOKENS])) {
                    $this->resolveTokens($record[self::$_VALUE_TOKENS], $xrefTokenResolver);
                }
                if (isset($record[self::$_KEY_TOKENS])) {
                    $this->resolveTokens($record[self::$_KEY_TOKENS], $xrefTokenResolver);
                }
            }
            foreach ($lookup as &$record) {
                if (isset($record[self::$_VALUE])) {
                    $newValue = TokenInjector::injectString(
                        $record[self::$_VALUE],
                        $record[self::$_VALUE_TOKENS]
                    );
                    $record[self::$_VALUE_REF] = $newValue;
                    unset($record[self::$_VALUE_TOKENS]);
                    unset($record[self::$_VALUE_REF]);
                }
                if (isset($record[self::$_KEY])) {
                    $oldKey = $record[self::$_KEY];
                    $newKey = TokenInjector::injectString(
                        $oldKey,
                        $record[self::$_KEY_TOKENS]
                    );
                    unset($record[self::$_KEY_TOKENS]);
                    if ($oldKey != $newKey) {
                        $record[self::$_KEY_REF][$newKey] = $record[self::$_KEY_REF][$oldKey];
                        unset($record[self::$_KEY_REF][$oldKey]);
                        unset($record[self::$_KEY_REF]);
                    }
                }
            }
            unset($record);
        }
    }
}