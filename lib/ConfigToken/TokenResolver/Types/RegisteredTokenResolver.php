<?php

namespace ConfigToken\TokenResolver\Types;

use ConfigToken\TokenResolver\Exception\UnknownTokenException;


/**
 * Resolves token values based on a given uni-dimensional associative array (token name => value).
 */
class RegisteredTokenResolver extends AbstractTokenResolver
{
    /** @var string[] */
    protected $registeredTokenValues = array();

    public function __construct(array $registeredTokenValues = null)
    {
        if (isset($registeredTokenValues)) {
            $this->setRegisteredTokenValues($registeredTokenValues);
        }
    }

    /**
     * Get the token resolver type identifier.
     *
     * @return string
     */
    public static function getType()
    {
        return self::getBaseType();
    }

    /**
     * Get the token resolver base type identifier.
     *
     * @return string
     */
    public static function getBaseType()
    {
        return 'registered';
    }

    /**
     * Check if the token value with the given name is registered.
     *
     * @param string $tokenName The identifier of the token value.
     * @return boolean
     */
    public function isTokenValueRegistered($tokenName)
    {
        return isset($this->registeredTokenValues[$tokenName]);
    }
    
    /**
     * Get the token value registered with the given name.
     *
     * @param string $tokenName The name of the token value.
     * @return string|null If there is no token value registered with the given name.
     */
    public function getRegisteredTokenValue($tokenName)
    {
        if (!$this->isTokenValueRegistered($tokenName)) {
            return null;
        }
        return $this->registeredTokenValues[$tokenName];
    }

    /**
     * Check if any token values are registered.
     *
     * @return boolean
     */
    public function hasRegisteredTokenValues()
    {
        return count($this->registeredTokenValues) > 0;
    }

    /**
     * Get all registered token values.
     *
     * @return string[] With names as keys.
     */
    public function getRegisteredTokenValues()
    {
        return $this->registeredTokenValues;
    }

    /**
     * Set all registered token values.
     *
     * @param string[] $tokenValues Token values with names as keys.
     * @return $this
     */
    public function setRegisteredTokenValues($tokenValues)
    {
        $this->registeredTokenValues = $tokenValues;

        return $this;
    }

    /**
     * Register a new token value.
     *
     * @param string $tokenName The name of the token value to be registered.
     * @param string $tokenValue The token value to be registered.
     * @return $this
     */
    public function registerTokenValue($tokenName, $tokenValue)
    {
        $this->registeredTokenValues[$tokenName] = $tokenValue;
        return $this;
    }
    
    /**
     * Un-register a token value registered with the given name.
     *
     * @param string $tokenName The name of the token value to be unregistered.
     * @throws UnknownTokenException If there is no token value registered with the given name.
     * @return string The un-registered token value.
     */
    public function unRegisterTokenValue($tokenName)
    {
        if (!$this->isTokenValueRegistered($tokenName)) {
            throw new UnknownTokenException(
                sprintf(
                    'There is no token value registered with the name "%s".',
                    $tokenName
                )
            );
        }
        $unRegisteredTokenValue = $this->registeredTokenValues[$tokenName];
        unset($this->registeredTokenValues[$tokenName]);
        return $unRegisteredTokenValue;
    }

    /**
     * Get the value for the given token.
     *
     * @param string $tokenName The name of the token to be resolved to a value.
     * @param boolean|null $ignoreUnknownTokens If True, passing an unresolvable token will not cause an exception.
     * @param string|null $defaultValue The value returned if the token is not found and set to ignore unknown tokens.
     * @throws UnknownTokenException If the token name was not registered and set not to ignore unknown tokens.
     * @return string|null
     */
    public function getTokenValue($tokenName, $ignoreUnknownTokens = null, $defaultValue = Null)
    {
        if (is_null($ignoreUnknownTokens)) {
            $ignoreUnknownTokens = $this->getIgnoreUnknownTokens();
        }
        if ($this->isTokenValueRegistered($tokenName)) {
            return $this->getRegisteredTokenValue($tokenName);
        }
        if ($ignoreUnknownTokens) {
            if (!$this->hasRegisteredTokenValues()) {
                trigger_error('The JSON token resolver has no known values.', E_USER_WARNING);
            }
            return $defaultValue;
        }
        throw new UnknownTokenException($tokenName);
    }
}