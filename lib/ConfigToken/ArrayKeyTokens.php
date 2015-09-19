<?php

namespace ConfigToken;


use ConfigToken\TokenResolver\TokenResolverInterface;

class ArrayKeyTokens implements DisposableInterface, ResolvableTokenInterface
{
    /** @var TokenCollection */
    public $tokens;
    public $key;
    public $keyRef;

    /**
     * Attempt to resolve the values for all tokens in the list.
     *
     * @param TokenResolverInterface $tokenResolver
     * @param boolean|null $ignoreUnknownTokens Null to use token resolver option.
     * @param boolean|null $ignoreUnknownFilters Null to use collection option.
     * @return $this
     */
    public function resolve(TokenResolverInterface $tokenResolver, $ignoreUnknownTokens = null,
                            $ignoreUnknownFilters = null)
    {
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

    public function release()
    {
        $this->keyRef = null;
        $this->tokens = null;
        $this->keyRef = null;
    }
}