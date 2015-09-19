<?php

namespace ConfigToken\TokenResolver\Types;


use ConfigToken\TokenResolver\Exception\UnknownTokenException;

/**
 * Token resolver to provide values based on a given uni-dimensional associative array (token name => value).
 *
 * @package ConfigToken\TokenResolver\Types
 */
class RegisteredTokenResolver extends AbstractTokenResolver
{
    const TYPE = 'registered';
    const VALUES = 'values';

    /**
     * Override to specify default option values.
     *
     * @return array
     */
    protected static function getDefaultOptions()
    {
        return array_merge(
            parent::getDefaultOptions(),
            array(
                static::VALUES => array(),
            )
        );
    }

    /**
     * Create a new registered token resolver with the given known token values and options.
     *
     * @param array|null $values Associative array of registered token values. Null for empty.
     * @param array|null $options Associative array of option values.
     */
    public function __construct(array $values = null, array $options = null)
    {
        parent::__construct($options);
        if (isset($values)) {
            $this->setValues($values);
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
        return static::TYPE;
    }

    /**
     * Check if the registered token values were set and not empty.
     *
     * @return boolean
     */
    public function hasValues()
    {
        return count($this->getValues()) > 0;
    }

    /**
     * Get the registered token values.
     *
     * @return array|null
     */
    public function getValues()
    {
        return $this->getOption(self::VALUES);
    }

    /**
     * Set the registered token values.
     *
     * @param array $value The new value.
     * @return $this
     */
    public function setValues($value)
    {
        return $this->setOption(self::VALUES, $value);
    }

    /**
     * Check if the token value with the given name is registered.
     *
     * @param string $tokenName The identifier of the token value.
     * @return boolean
     */
    public function hasValue($tokenName)
    {
        $values = $this->getValues();
        return isset($values[$tokenName]);
    }
    
    /**
     * Get the token value registered with the given name.
     *
     * @param string $tokenName The name of the token value.
     * @param string|null $default The default value to return if no value registered for given token name.
     * @return string|null If there is no token value registered with the given name.
     */
    public function getValue($tokenName, $default = null)
    {
        if (!$this->hasValue($tokenName)) {
            return $default;
        }
        $values = $this->getValues();
        return $values[$tokenName];
    }

    /**
     * Register a new token value.
     *
     * @param string $tokenName The name of the token value to be registered.
     * @param string|null $tokenValue The token value to be registered. If null, token value will be unregistered.
     * @return $this
     */
    public function setValue($tokenName, $tokenValue = null)
    {
        $this->options[static::VALUES][$tokenName] = $tokenValue;
        return $this;
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
    public function getValueForToken($tokenName, $ignoreUnknownTokens = null, $defaultValue = Null)
    {
        if (is_null($ignoreUnknownTokens)) {
            $ignoreUnknownTokens = $this->getIgnoreUnknownTokens();
        }
        if ($this->hasValue($tokenName)) {
            return $this->getValue($tokenName);
        }
        if ($ignoreUnknownTokens) {
            if (!$this->hasValues()) {
                trigger_error('The token resolver has no known values.', E_USER_WARNING);
            }
            return $defaultValue;
        }
        throw new UnknownTokenException($tokenName);
    }
}