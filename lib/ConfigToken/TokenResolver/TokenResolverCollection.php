<?php

namespace ConfigToken\TokenResolver;


/**
 * Collection of token resolver instances.
 *
 * @package ConfigToken\TokenResolver
 */
class TokenResolverCollection
{
    /** @var TokenResolverInterface[] */
    protected $collection = array();

    protected static $_KEY = 0;
    protected static $_KEY_TOKENS = 1;
    protected static $_KEY_REF = 2;
    protected static $_VALUE = 3;
    protected static $_VALUE_TOKENS = 4;
    protected static $_VALUE_REF = 5;

    public function add(TokenResolverInterface $xrefTokenResolver)
    {
        $this->collection[] = $xrefTokenResolver;
    }

    public function addCollection(TokenResolverCollection $xrefTokenResolverCollection)
    {
        $this->collection = array_merge($this->collection, $xrefTokenResolverCollection->collection);
    }

    public function prepend(TokenResolverInterface $xrefTokenResolver)
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
        foreach ($this->collection as $tokenResolver) {
            $tokenParser = $tokenResolver->getTokenParser();
            $lookup = $tokenParser->parseArray($array);
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