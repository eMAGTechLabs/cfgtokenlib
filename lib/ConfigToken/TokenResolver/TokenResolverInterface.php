<?php

namespace ConfigToken\TokenResolver;


/**
 * Interface to create custom token value resolvers.
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
     * Check if the token value with the given name is registered.
     *
     * @param string $tokenName The identifier of the token value.
     * @return boolean
     */
    public function isTokenValueRegistered($tokenName);

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
     * @return boolean
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
    public function getTokenValue($tokenName, $ignoreUnknownTokens = null, $defaultValue = Null);
}