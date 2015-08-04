<?php

namespace ConfigToken;

/**
 * Configurable string parser class used to extract a collection of tokens.
 */
class TokenParser
{
    /** @var string */
    protected $filterDelimiter = '|';

    /** @var string */
    protected $tokenRegex;

    /**
     * @param string $filterDelimiter The string used to separate the names of the filters in the token.
     * @param string|null $tokenRegex If null, tokenPrefix and tokenSuffix will be used to create the tokenRegex.
     * @param string $tokenPrefix The token prefix used to create the tokenRegex.
     * @param string $tokenSuffix The token suffix used to create the tokenRegex.
     */
    public function __construct($filterDelimiter = '|', $tokenRegex = null, $tokenPrefix = '[[', $tokenSuffix = ']]')
    {
        $this->setFilterDelimiter($filterDelimiter);
        if (isset($tokenRegex)) {
            $this->setTokenRegex($tokenRegex);
        } else {
            $this->setTokenRegex(static::getTokenRegexByDelimiters($tokenPrefix, $tokenSuffix));
        }
    }

    /**
     * Get filter delimiter.
     *
     * @return string
     */
    public function getFilterDelimiter()
    {
        return $this->filterDelimiter;
    }

    /**
     * Set filter delimiter.
     *
     * @param string $value The new value.
     * @return $this
     */
    public function setFilterDelimiter($value)
    {
        $this->filterDelimiter = $value;
        return $this;
    }

    /**
     * Get token regex.
     *
     * @return string
     */
    public function getTokenRegex()
    {
        return $this->tokenRegex;
    }

    /**
     * Set token regex.
     *
     * @param string $value The new value.
     * @return $this
     */
    public function setTokenRegex($value)
    {
        $this->tokenRegex = $value;
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

    /**
     * Parse the given string and extract all tokens.
     *
     * @param string $string The string to parse.
     * @return TokenCollection
     */
    public function parseString($string)
    {
        $result = new TokenCollection();

        $pregResults = array();
        $pregResult = preg_match_all($this->tokenRegex, $string, $pregResults, PREG_OFFSET_CAPTURE);
        if (!$pregResult) {
            return $result;
        }
        unset($pregResult);

        $result->setSourceHash(md5($string));

        foreach ($pregResults[1] as $key => $pregResult) {
            $tokenString = $pregResults[0][$key][0];
            if ($result->has($tokenString)) {
                $result->get($tokenString)->addOffset($pregResults[0][$key][1]);
                continue;
            }
            $filters = explode($this->filterDelimiter, $pregResult[0]);
            $tokenName = $filters[0];
            unset($filters[0]);

            $token = new Token(
                $pregResults[0][$key][1],
                $tokenString,
                $tokenName,
                null, // $tokenValue
                $filters
            );

            $result->add($token);
        }

        return $result;
    }
}