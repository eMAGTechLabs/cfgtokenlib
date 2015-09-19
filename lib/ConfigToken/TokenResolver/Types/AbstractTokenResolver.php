<?php

namespace ConfigToken\TokenResolver\Types;


use ConfigToken\TokenParser;
use ConfigToken\TokenResolver\TokenResolverInterface;

/**
 * Abstract Token Resolver class to serve as base for other token resolver implementations.
 *
 * @package ConfigToken\TokenResolver\Types
 */
abstract class AbstractTokenResolver implements TokenResolverInterface
{
    const IGNORE_UNKNOWN_TOKENS = 'ignore-unknown-tokens';

    /** @var array */
    protected $options;

    /** @var TokenParser */
    protected $tokenParser;

    /**
     * Get the name of the implementation class.
     *
     * @return string
     */
    public static function getClassName()
    {
        return get_called_class();
    }

    /**
     * Override to specify default option values.
     *
     * @return array
     */
    protected static function getDefaultOptions()
    {
        return array(
            static::IGNORE_UNKNOWN_TOKENS => true
        );
    }

    /**
     * Initialize the token resolver with the given options.
     *
     * @param array|null $options Associative array of option values.
     */
    protected function __construct(array $options = null)
    {
        if (isset($options)) {
            $this->options = array_merge(static::getDefaultOptions(), $options);
        } else {
            $this->options = static::getDefaultOptions();
        }
    }

    /**
     * Check if the associated token parser was set.
     *
     * @return boolean
     */
    public function hasTokenParser()
    {
        return isset($this->tokenParser);
    }

    /**
     * Get the associated token parser.
     *
     * @return TokenParser|null
     */
    public function getTokenParser()
    {
        if (!$this->hasTokenParser()) {
            return null;
        }
        return $this->tokenParser;
    }

    /**
     * Set the associated token parser.
     *
     * @param TokenParser $value The new value.
     * @return $this
     */
    public function setTokenParser($value)
    {
        $this->tokenParser = $value;
        return $this;
    }
    /**
     * Get the ignore unknown tokens flag.
     * If this flag is false, getValue will throw an exception
     * if a value for the requested token could not be provided by this resolver.
     *
     * @return boolean
     */
    public function getIgnoreUnknownTokens()
    {
        return $this->getOption(self::IGNORE_UNKNOWN_TOKENS);
    }

    /**
     * Set the ignore unknown tokens flag.
     * If this flag is false, getValue will throw an exception
     * if a value for the requested token could not be provided by this resolver.
     *
     * @param boolean $value The new value.
     * @return $this
     */
    public function setIgnoreUnknownTokens($value)
    {
        return $this->setOption(self::IGNORE_UNKNOWN_TOKENS, $value);
    }
}