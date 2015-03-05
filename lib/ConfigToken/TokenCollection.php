<?php

namespace ConfigToken;

use ConfigToken\TokenFilter\Exception\UnknownFilterException;
use ConfigToken\TokenResolver\Exception\OutOfScopeException;
use ConfigToken\TokenResolver\Exception\ScopeTokenValueSerializationException;
use ConfigToken\TokenResolver\Exception\TokenFormatException;
use ConfigToken\TokenResolver\Exception\UnknownTokenException;
use ConfigToken\TokenResolver\TokenResolverInterface;

/**
 * Generic token collection class used in conjunction with a given token resolver instance to resolve
 * tokens to values and apply the given filters.
 */
class TokenCollection implements \IteratorAggregate
{
    /** @var Token[] */
    protected $tokens =array ();

    /** @var string */
    protected $sourceHash;

    /**
     * @param Token[] $tokens Array of Token with tokenString as keys.
     */
    function __construct(array $tokens = null)
    {
        if (isset($tokens)) {
            $this->tokens = $tokens;
        }
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return \Traversable An instance of an object implementing <b>Iterator</b> or <b>Traversable</b>
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->tokens);
    }


    /**
     * Check if the source hash was set.
     *
     * @return boolean
     */
    public function hasSourceHash()
    {
        return isset($this->sourceHash);
    }

    /**
     * Get the source hash.
     *
     * @return string|null
     */
    public function getSourceHash()
    {
        if (!$this->hasSourceHash()) {
            return null;
        }
        return $this->sourceHash;
    }

    /**
     * Set the source hash.
     *
     * @param string $value The new value.
     * @return $this
     */
    public function setSourceHash($value)
    {
        $this->sourceHash = $value;
        return $this;
    }

    /**
     * Check if the list of tokens is empty.
     *
     * @return boolean
     */
    public function isEmpty()
    {
        return count($this->tokens) == 0;
    }

    /**
     * Get the number of tokens in the list
     *
     * @return integer
     */
    public function getCount()
    {
        return count($this->tokens);
    }

    /**
     * Add a new token to the list.
     * If another token already exists with the same tokenString then it will be overwritten.
     *
     * @param Token $token
     * @return $this
     */
    public function add(Token $token)
    {
        $this->tokens[$token->getTokenString()] = $token;

        return $this;
    }

    /**
     * Import add all tokens from another list.
     * Existing tokens will be overwritten. The hash will be unset.
     *
     * @param TokenCollection $tokens
     * @return $this
     */
    public function import(TokenCollection $tokens)
    {
        foreach ($tokens->tokens as $tokenString => $token) {
            $this->tokens[$tokenString] = $token;
        }
        $this->sourceHash = null;

        return $this;
    }

    /**
     * Remove the given token from the list.
     *
     * @param Token $token
     * @return $this
     */
    public function remove(Token $token)
    {
        unset($this->tokens[$token->getTokenString()]);
        return $this;
    }

    /**
     * Clear the list of tokens.
     *
     * @return $this
     */
    public function clear()
    {
        $this->tokens = array();
        return $this;
    }

    /**
     * Check if a token with the given token string is in this list.
     *
     * @param string $tokenString
     * @return boolean
     */
    public function has($tokenString)
    {
        return isset($this->tokens[$tokenString]);
    }

    /**
     * Get a token from this list having the given token string.
     *
     * @param string $tokenString
     * @return Token
     */
    public function get($tokenString)
    {
        return $this->tokens[$tokenString];
    }

    /**
     * Get the list of tokens as an associative array of Token with tokenString as keys.
     *
     * @return Token[]
     */
    public function getArray()
    {
        return $this->tokens;
    }

    /**
     * Find tokens having the given token name.
     *
     * @param string|string[]|null $tokenNameToFind Null to find all. String to find a certain token. Array of strings
     *                                              To find multiple tokens.
     * @return Token[]|array Array of Token if $tokenNameToFind is string, Array of arrays of Token with token names
     *                       as keys if $tokenNameToFind is string[] or null.
     */
    public function findByName($tokenNameToFind = null)
    {
        $result = array();
        foreach ($this->tokens as $tokenString => $token)
        {
            $tokenName = $token->getTokenName();
            if ((!isset($tokenNameToFind)) || (is_array($tokenNameToFind) && in_array($tokenName, $tokenNameToFind)) ||
                ($tokenName == $tokenNameToFind)) {
                if (!isset($result[$tokenName])) {
                    $result[$tokenName] = array();
                }
                $result[$tokenName][$tokenString] = $token;
            }
        }
        if (isset($tokenNameToFind) && isset($result[$tokenNameToFind])) {
            return $result[$tokenNameToFind];
        }
        return $result;
    }

    /**
     * Find all resolved tokens.
     *
     * @return Token[]
     */
    public function findResolved()
    {
        $result = array();
        foreach ($this->tokens as $tokenString => $token)
        {
            if ($token->getIsResolved()) {
                $result[$tokenString] = $token;
            }
        }
        return $result;
    }

    /**
     * Find all tokens resolved but not yet injected.
     *
     * @return Token[]
     */
    public function findResolvedAndNotInjected()
    {
        $result = array();
        foreach ($this->tokens as $tokenString => $token)
        {
            if ($token->getIsResolved() && (!$token->getIsInjected())) {
                $result[$tokenString] = $token;
            }
        }
        return $result;
    }

    /**
     * Find all unresolved tokens.
     *
     * @return Token[]
     */
    public function findUnresolved()
    {
        $result = array();
        foreach ($this->tokens as $tokenString => $token)
        {
            if (!$token->getIsResolved()) {
                $result[$tokenString] = $token;
            }
        }
        return $result;
    }

    /**
     * Check if at least one unresolved token exists in the list.
     *
     * @return boolean
     */
    public function hasUnresolved()
    {
        foreach ($this->tokens as $tokenString => $token)
        {
            if (!$token->getIsResolved()) {
                return True;
            }
        }
        return False;
    }

    /**
     * Find all tokens with unresolved filters.
     *
     * @return Token[]
     */
    public function findWithUnresolvedFilters()
    {
        $result = array();
        foreach ($this->tokens as $tokenString => $token)
        {
            if ($token->hasUnresolvedFilters()) {
                $result[$tokenString] = $token;
            }
        }
        return $result;
    }

    /**
     * Attempt to resolve the values for all tokens in the list.
     *
     * @param TokenResolverInterface $tokenResolver
     * @param boolean $ignoreUnknownTokens
     * @param boolean $ignoreUnknownFilters
     * @throws UnknownFilterException
     *
     * If using RegisteredTokenResolver:
     * @throws UnknownTokenException If the token name was not registered and set not to ignore unknown tokens.
     *
     * If using ScopeTokenResolver:
     * @throws UnknownTokenException
     * @throws OutOfScopeException
     * @throws ScopeTokenValueSerializationException
     * @throws TokenFormatException
     * @return $this
     */
    public function resolve(TokenResolverInterface $tokenResolver,
                            $ignoreUnknownTokens = True, $ignoreUnknownFilters = True)
    {
        $unfilteredValues = array();
        foreach ($this->tokens as $tokenString => $token) {
            if ($token->getIsResolved() && (!$token->hasUnresolvedFilters())) {
                continue;
            }
            $unfilteredValueChanged = False;
            if (!$token->getIsResolved()) {
                $tokenName = $token->getTokenName();
                if (array_key_exists($tokenName, $unfilteredValues)) {
                    $token->setUnfilteredTokenValue($unfilteredValues[$tokenName]);
                } else {
                    $token->resolveUnfilteredValue($tokenResolver, $ignoreUnknownTokens);
                    $unfilteredValues[$tokenName] = $token->getUnfilteredTokenValue();
                }
                $unfilteredValueChanged = True;
            }
            if ($token->hasFilters() && $token->hasUnfilteredTokenValue() && $unfilteredValueChanged) {
                $token->applyFilters($ignoreUnknownFilters);
            }
        }
        return $this;
    }

}