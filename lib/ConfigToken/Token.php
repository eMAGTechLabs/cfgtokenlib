<?php

namespace ConfigToken;

use ConfigToken\TokenFilter\TokenFilterFactory;
use ConfigToken\TokenFilter\Exception\UnknownFilterException;
use ConfigToken\TokenResolver\Exception\OutOfScopeException;
use ConfigToken\TokenResolver\Exception\ScopeTokenValueSerializationException;
use ConfigToken\TokenResolver\Exception\TokenFormatException;
use ConfigToken\TokenResolver\Exception\UnknownTokenException;
use ConfigToken\TokenResolver\TokenResolverInterface;


class Token
{
    /** @var integer[] */
    protected $offsets;
    /** @var string eg. [[tokenName|filter]] */
    protected $tokenString;
    /** @var string eg. tokenName */
    protected $tokenName;
    /** @var mixed */
    protected $tokenValue = null;
    /** @var mixed */
    protected $unfilteredTokenValue = null;
    /** @var boolean */
    protected $isResolved = False;
    /** @var boolean */
    protected $isFiltered = False;
    /** @var boolean */
    protected $isInjected = False;
    /** @var string[] */
    protected $filters;
    /** @var string[] */
    protected $unresolvedFilters;

    /**
     * @param integer $offset The offset where the token string is positioned in the string to be injected.
     * @param string $tokenString The token string. (eg. [[tokenName|filter1]])
     * @param string $tokenName The name part of the token string.
     * @param string|null $tokenValue The resolved value of the token. Null if not resolved.
     * @param array $filters Array of filter names applied to the value.
     * @param array $unresolvedFilters Array of filter names that could not be applied to the value.
     */
    function __construct($offset, $tokenString, $tokenName, $tokenValue = null, array $filters = array(),
                         array $unresolvedFilters = array())
    {
        $this->tokenString = $tokenString;
        $this->setTokenName($tokenName);
        $this->setUnfilteredTokenValue(null);
        $this->setTokenValue($tokenValue);
        $this->setOffsets(array($offset => $offset));
        $this->setFilters($filters);
        $this->setUnresolvedFilters($unresolvedFilters);
    }

    /**
     * Get the token string.
     *
     * @return string
     */
    public function getTokenString()
    {
        return $this->tokenString;
    }

    /**
     * Get the token name.
     *
     * @return string
     */
    public function getTokenName()
    {
        return $this->tokenName;
    }

    /**
     * Set token name.
     *
     * @param string $value The new value.
     * @return $this
     */
    public function setTokenName($value)
    {
        $this->tokenName = $value;
        return $this;
    }

    /**
     * Check if the token value was set.
     *
     * @return boolean
     */
    public function hasTokenValue()
    {
        return isset($this->tokenValue);
    }

    /**
     * Get the token value.
     *
     * @return string|null
     */
    public function getTokenValue()
    {
        if (!$this->hasTokenValue()) {
            return null;
        }
        return $this->tokenValue;
    }

    /**
     * Set the token value.
     *
     * @param string $value The new value.
     * @return $this
     */
    public function setTokenValue($value)
    {
        if (isset($value) && (!isset($this->unfilteredTokenValue))) {
            $this->setUnfilteredTokenValue($value);
        }
        $this->tokenValue = $value;
        $this->setIsFiltered(isset($value));
        return $this;
    }

    /**
     * Check if the unfiltered token value was set.
     *
     * @return boolean
     */
    public function hasUnfilteredTokenValue()
    {
        return isset($this->unfilteredTokenValue);
    }

    /**
     * Get the unfiltered token value.
     *
     * @return string|null
     */
    public function getUnfilteredTokenValue()
    {
        if (!$this->hasUnfilteredTokenValue()) {
            return null;
        }
        return $this->unfilteredTokenValue;
    }

    /**
     * Set the unfiltered token value.
     *
     * @param string $value The new value.
     * @return $this
     */
    public function setUnfilteredTokenValue($value)
    {
        $this->unfilteredTokenValue = $value;
        $this->tokenValue = $value;
        $this->setIsResolved(isset($value));
        $this->setIsFiltered(False);
        $this->setUnresolvedFilters($this->filters);
        return $this;
    }

    /**
     * Get if the token value is resolved.
     *
     * @return boolean
     */
    public function getIsResolved()
    {
        return $this->isResolved;
    }

    /**
     * Set if the token value is resolved.
     *
     * @param boolean $value The new value.
     * @return $this
     */
    public function setIsResolved($value)
    {
        $this->isResolved = $value;
        return $this;
    }

    /**
     * Get if the token value is filtered.
     *
     * @return boolean
     */
    public function getIsFiltered()
    {
        return $this->isFiltered;
    }

    /**
     * Set if the token value is filtered.
     *
     * @param boolean $value The new value.
     * @return $this
     */
    public function setIsFiltered($value)
    {
        $this->isFiltered = $value;
        return $this;
    }

    /**
     * Get if the token value was injected.
     *
     * @return boolean
     */
    public function getIsInjected()
    {
        return $this->isInjected;
    }

    /**
     * Set if the token value was injected.
     *
     * @param boolean $value The new value.
     * @return $this
     */
    public function setIsInjected($value)
    {
        $this->isInjected = $value;
        return $this;
    }

    /**
     * Get the array of offsets.
     *
     * @return integer[]
     */
    public function getOffsets()
    {
        return $this->offsets;
    }

    /**
     * Set the array of offsets.
     *
     * @param integer[] $value The new array of offsets.
     * @return $this
     */
    public function setOffsets($value)
    {
        $this->offsets = $value;
        return $this;
    }

    /**
     * Add a new offset.
     *
     * @param integer $offset
     * @return $this
     */
    public function addOffset($offset)
    {
        $this->offsets[$offset] = $offset;
        return $this;
    }

    /**
     * Adjust the given offset by adding the given delta value.
     *
     * @param integer $offset
     * @param integer $delta
     * @return $this
     */
    public function adjustOffset($offset, $delta)
    {
        if ($delta == 0) {
            return $this;
        }
        unset($this->offsets[$offset]);
        $newOffset = $offset + $delta;
        $this->offsets[$newOffset] = $newOffset;
        return $this;
    }

    /**
     * Check if the filters array was set.
     *
     * @return boolean
     */
    public function hasFilters()
    {
        return !empty($this->filters);
    }

    /**
     * Get the filters array.
     *
     * @return string[]
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * Set the filters array.
     *
     * @param string[] $value The new value.
     * @return $this
     */
    public function setFilters($value)
    {
        $this->filters = $value;
        return $this;
    }

    /**
     * Check if the unresolved filters array was set.
     *
     * @return boolean
     */
    public function hasUnresolvedFilters()
    {
        return !empty($this->unresolvedFilters);
    }

    /**
     * Get the unresolved filters array.
     *
     * @return string[]
     */
    public function getUnresolvedFilters()
    {
        return $this->unresolvedFilters;
    }

    /**
     * Set the unresolved filters array.
     *
     * @param string[] $value The new value.
     * @return $this
     */
    public function setUnresolvedFilters($value)
    {
        $this->unresolvedFilters = $value;
        return $this;
    }

    /**
     * @param TokenResolverInterface $tokenResolver
     * @param boolean $ignoreUnknownTokens
     * @return $this
     */
    public function resolveUnfilteredValue(TokenResolverInterface $tokenResolver, $ignoreUnknownTokens = True)
    {
        $tokenValueUnfiltered = $tokenResolver->getTokenValue(
            $this->tokenName,
            $ignoreUnknownTokens,
            $this->unfilteredTokenValue
        );

        $this->setUnfilteredTokenValue($tokenValueUnfiltered);
        return $this;
    }

    /**
     * @param boolean $ignoreUnknownFilters
     * @throws UnknownFilterException
     * @return $this
     */
    public function applyFilters($ignoreUnknownFilters = True)
    {
        $unresolvedFilters = array();
        $filteredValue = $this->unfilteredTokenValue;
        foreach ($this->filters as $filterName) {
            if (!TokenFilterFactory::isRegisteredByName($filterName)) {
                if (!$ignoreUnknownFilters) {
                    throw new UnknownFilterException($filterName);
                }
                $unresolvedFilters[$filterName] = $filterName;
                continue;
            }
            $filteredValue = TokenFilterFactory::getFilteredValue($filterName, $filteredValue, $ignoreUnknownFilters);
        }
        $this->setTokenValue($filteredValue);
        $this->unresolvedFilters = $unresolvedFilters;
        return $this;
    }

    /**
     * Attempt to resolve and filter the token value.
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
    public function resolve(TokenResolverInterface $tokenResolver, $ignoreUnknownTokens = True,
                            $ignoreUnknownFilters = True)
    {
        if ($this->isResolved && (!$this->hasUnresolvedFilters())) {
            return $this;
        }
        if (!$this->isResolved) {
            $this->resolveUnfilteredValue($tokenResolver, $ignoreUnknownTokens);
        }
        if ($this->hasUnfilteredTokenValue() && $this->hasUnresolvedFilters()) {
            $this->applyFilters($ignoreUnknownFilters);
        }
        return $this;
    }
}