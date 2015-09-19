<?php

namespace ConfigToken\TokenResolver;
use ConfigToken\TokenParser;


/**
 * Interface for token value resolvers.
 *
 * @package ConfigToken\TokenResolver
 */
interface TokenResolverInterface
{
    /**
     * Get the token resolver type identifier.
     *
     * @return string
     */
    public static function getType();

    /**
     * Get the name of the implementation class.
     *
     * @return string
     */
    public static function getClassName();

    /**
     * Get the token resolver base type identifier.
     *
     * @return string
     */
    public static function getBaseType();

    /**
     * Check if an associated token parser has been set.
     *
     * @return boolean
     */
    public function hasTokenParser();

    /**
     * Get the associated token parser.
     *
     * @return TokenParser|null
     */
    public function getTokenParser();

    /**
     * Set the associated token parser.
     *
     * @return $this
     */
    public function setTokenParser();

    /**
     * Check if the token value with the given name is registered.
     *
     * @param string $tokenName The identifier of the token value.
     * @return boolean
     */
    public function hasValue($tokenName);

    /**
     * Get the state of the ignore unknown tokens flag.
     *
     * @return boolean
     */
    public function getIgnoreUnknownTokens();

    /**
     * Set the state of the ignore unknown tokens flag.
     *
     * @param boolean $value
     * @return $this
     */
    public function setIgnoreUnknownTokens($value);

    /**
     * Get the value for the given token.
     *
     * @param string $tokenName The name of the token to be resolved to a value.
     * @param boolean|null $ignoreUnknownTokens If True, passing an unresolvable token will not cause an exception.
     * @param string|null $defaultValue The value returned if the token is not found and set to ignore unknown tokens.
     * @return string|null
     */
    public function getValueForToken($tokenName, $ignoreUnknownTokens = null, $defaultValue = Null);

    /**
     * Parses the tokens in the given string with the associated parser and attempts to resolve them.
     *
     * @param string $string The string to be parsed.
     * @param boolean|null $ignoreUnknownTokens If True, unresolvable tokens will not cause exceptions.
     * @return mixed
     */
    public function resolveString($string, $ignoreUnknownTokens = null);

    /**
     * Parses the tokens in the given associative array with the associated parser and attempts to resolve them.
     *
     * @param array $array The associative array to be parsed.
     * @param boolean|null $ignoreUnknownTokens If True, unresolvable tokens will not cause exceptions.
     * @return mixed
     */
    public function resolveArray(array &$array, $ignoreUnknownTokens = null);
}