<?php

namespace ConfigToken;


use ConfigToken\Exception\OptionsMissingException;

abstract class AbstractTokenParser implements TokenParserInterface
{
    const FILTER_DELIMITER = 'filter-delimiter';
    const TOKEN_REGEX = 'token-regex';
    const TOKEN_PREFIX = 'token-prefix';
    const TOKEN_SUFFIX = 'token-suffix';

    /** @var Options */
    protected $options;

    protected static $DEFAULT_OPTIONS = array(
        self::FILTER_DELIMITER => '|',
        self::TOKEN_PREFIX => '{{',
        self::TOKEN_SUFFIX => '}}',
    );

    /**
     * Create a new token parser instance.
     *
     * @param Options|null $options The options bag.
     * @param string $filterDelimiter The string used to separate the names of the filters in the token.
     * @param string|null $tokenRegex If null, tokenPrefix and tokenSuffix will be used to create the tokenRegex.
     * @param string $tokenPrefix The token prefix used to create the tokenRegex.
     * @param string $tokenSuffix The token suffix used to create the tokenRegex.
     */
    public function __construct(Options $options = null, $filterDelimiter = null, $tokenRegex = null,
                                $tokenPrefix = null, $tokenSuffix = null)
    {
        if (!isset($options)) {
            $options = new Options(static::$DEFAULT_OPTIONS);
        } else {
            $options->setDefaults(static::$DEFAULT_OPTIONS);
        }
        $this->options = $options;
        if (isset($filterDelimiter)) {
            $this->setFilterDelimiter($filterDelimiter);
        }
        if (isset($tokenRegex)) {
            $this->setTokenRegex($tokenRegex);
        } else if (isset($tokenPrefix) && isset($tokenSuffix)) {
            $this->setTokenRegex(static::getTokenRegexByDelimiters($tokenPrefix, $tokenSuffix));
        }
    }

    /**
     * Get the aggregated options bag.
     *
     * @return Options
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Validate the given options bag.
     *
     * @param Options $options
     * @throws OptionsMissingException
     */
    public static function validateOptions(Options $options)
    {
        $missingValues = $options->getMissingValues(
            array(
                static::FILTER_DELIMITER,
                static::TOKEN_REGEX
            )
        );
        if (!empty($missingValues)) {
            throw new OptionsMissingException($missingValues);
        }
    }

    /**
     * Get filter delimiter.
     *
     * @return string
     */
    public function getFilterDelimiter()
    {
        return $this->options->getValue(static::FILTER_DELIMITER);
    }

    /**
     * Set filter delimiter.
     *
     * @param string $value The new value.
     * @return $this
     */
    public function setFilterDelimiter($value)
    {
        $this->options->setValue(static::FILTER_DELIMITER, $value);
        return $this;
    }

    /**
     * Get token regex.
     *
     * @return string
     */
    public function getTokenRegex()
    {
        return $this->options->getValue(static::TOKEN_REGEX);
    }

    /**
     * Set token regex.
     *
     * @param string $value The new value.
     * @return $this
     */
    public function setTokenRegex($value)
    {
        $this->options->setValue(static::TOKEN_REGEX, $value);
        return $this;
    }

    /**
     * Create and return the token regex from the given prefix and suffix.
     *
     * @param string $prefix
     * @param string $suffix
     * @return $this
     */
    public static function getTokenRegexByDelimiters($prefix, $suffix)
    {
        return sprintf('/%s+(.*?)%s/', preg_quote($prefix), preg_quote($suffix));
    }

    /**
     * Create and set the token regex from the given prefix and suffix.
     *
     * @param string $prefix
     * @param string $suffix
     * @return $this
     */
    public function setTokenRegexByDelimiters($prefix, $suffix)
    {
        $this->setTokenRegex(static::getTokenRegexByDelimiters($prefix, $suffix));
        return $this;
    }
}