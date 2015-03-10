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
     * Get the value for the given token.
     *
     * @param string $tokenName The name of the token to be resolved to a value.
     * @param boolean $ignoreUnknownTokens If True, passing an unresolvable token will not cause an exception.
     * @param string|null $defaultValue The value returned if the token is not found and set to ignore unknown tokens.
     * @return string|null
     */
    public function getTokenValue($tokenName, $ignoreUnknownTokens = False, $defaultValue = Null);
}