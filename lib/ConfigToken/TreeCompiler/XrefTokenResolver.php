<?php

namespace ConfigToken\TreeCompiler;

use ConfigToken\Token;
use ConfigToken\TokenCollection;
use ConfigToken\TokenParser;
use ConfigToken\TokenResolver\TokenResolverInterface;
use ConfigToken\TokenResolver\Types\RegisteredTokenResolver;
use ConfigToken\TokenResolver\Types\ScopeTokenResolver;


class XrefTokenResolver
{
    /** @var TokenResolverInterface */
    protected $tokenResolver;
    /** @var Xref */
    protected $xref;
    /** @var array */
    protected $registeredTokenValues;
    /** @var boolean */
    protected $ignoreUnknownFilters;
    /** @var string */
    protected $tokenRegex;
    /** @var string */
    protected $tokenPrefix;
    /** @var string */
    protected $tokenSuffix;
    /** @var string */
    protected $tokenFilterDelimiter;
    /** @var TokenParser */
    protected $tokenParser;

    public function __construct(TokenResolverInterface $tokenResolver = null)
    {
        $this->setTokenResolver($tokenResolver);
    }

    /**
     * Check if token resolver was set.
     *
     * @return boolean
     */
    public function hasTokenResolver()
    {
        return isset($this->tokenResolver);
    }

    /**
     * Get token resolver.
     *
     * @return TokenResolverInterface|null
     */
    public function getTokenResolver()
    {
        if (!$this->hasTokenResolver()) {
            return null;
        }
        return $this->tokenResolver;
    }

    /**
     * Set token resolver.
     *
     * @param TokenResolverInterface|null $tokenResolver The new value for token resolver or null for none.
     * @return $this
     */
    public function setTokenResolver($tokenResolver = null)
    {
        $this->tokenResolver = $tokenResolver;
        return $this;
    }

    /**
     * Check if xref was set.
     *
     * @return boolean
     */
    public function hasXref()
    {
        return isset($this->xref);
    }

    /**
     * Get xref.
     *
     * @return Xref|null
     */
    public function getXref()
    {
        if (!$this->hasXref()) {
            return null;
        }
        return $this->xref;
    }

    /**
     * Set xref.
     *
     * @param Xref|null $xref The new value for xref or null for none.
     * @return $this
     */
    public function setXref($xref = null)
    {
        $this->xref = $xref;
        return $this;
    }

    /**
     * Check if registered token values was set.
     *
     * @return boolean
     */
    public function hasRegisteredTokenValues()
    {
        return isset($this->registeredTokenValues);
    }

    /**
     * Get registered token values.
     *
     * @return array|null
     */
    public function getRegisteredTokenValues()
    {
        if (!$this->hasRegisteredTokenValues()) {
            return null;
        }
        return $this->registeredTokenValues;
    }

    /**
     * Set registered token values.
     *
     * @param array|null $registeredTokenValues The new value for registered token values or null for none.
     * @return $this
     */
    public function setRegisteredTokenValues($registeredTokenValues = null)
    {
        $this->registeredTokenValues = $registeredTokenValues;
        return $this;
    }

    /**
     * Check if ignore unknown filters was set.
     *
     * @return boolean
     */
    public function hasIgnoreUnknownFilters()
    {
        return isset($this->ignoreUnknownFilters);
    }

    /**
     * Get ignore unknown filters.
     *
     * @return boolean|null
     */
    public function getIgnoreUnknownFilters()
    {
        if (!$this->hasIgnoreUnknownFilters()) {
            return null;
        }
        return $this->ignoreUnknownFilters;
    }

    /**
     * Set ignore unknown filters.
     *
     * @param boolean|null $ignoreUnknownFilters The new value for ignore unknown filters or null for default.
     * @return $this
     */
    public function setIgnoreUnknownFilters($ignoreUnknownFilters = null)
    {
        $this->ignoreUnknownFilters = $ignoreUnknownFilters;
        return $this;
    }

    /**
     * Check if token regex was set.
     *
     * @return boolean
     */
    public function hasTokenRegex()
    {
        return isset($this->tokenRegex);
    }

    /**
     * Get token regex.
     *
     * @return string|null
     */
    public function getTokenRegex()
    {
        if (!$this->hasTokenRegex()) {
            return null;
        }
        return $this->tokenRegex;
    }

    /**
     * Set token regex.
     *
     * @param string|null $tokenRegex The new value for token regex or null for default.
     * @return $this
     */
    public function setTokenRegex($tokenRegex = null)
    {
        $this->tokenRegex = $tokenRegex;
        if ($this->tokenRegex !== $tokenRegex) {
            $this->tokenParser = null;
        }
        return $this;
    }

    /**
     * Check if token prefix was set.
     *
     * @return boolean
     */
    public function hasTokenPrefix()
    {
        return isset($this->tokenPrefix);
    }

    /**
     * Get token prefix.
     *
     * @return string|null
     */
    public function getTokenPrefix()
    {
        if (!$this->hasTokenPrefix()) {
            return null;
        }
        return $this->tokenPrefix;
    }

    /**
     * Set token prefix.
     *
     * @param string|null $tokenPrefix The new value for token prefix or null for default.
     * @return $this
     */
    public function setTokenPrefix($tokenPrefix = null)
    {
        $this->tokenPrefix = $tokenPrefix;
        if ($this->tokenPrefix !== $tokenPrefix) {
            $this->tokenParser = null;
        }
        return $this;
    }

    /**
     * Check if token suffix was set.
     *
     * @return boolean
     */
    public function hasTokenSuffix()
    {
        return isset($this->tokenSuffix);
    }

    /**
     * Get token suffix.
     *
     * @return string|null
     */
    public function getTokenSuffix()
    {
        if (!$this->hasTokenSuffix()) {
            return null;
        }
        return $this->tokenSuffix;
    }

    /**
     * Set token suffix.
     *
     * @param string|null $tokenSuffix The new value for token suffix or null for default.
     * @return $this
     */
    public function setTokenSuffix($tokenSuffix = null)
    {
        $this->tokenSuffix = $tokenSuffix;
        if ($this->tokenSuffix !== $tokenSuffix) {
            $this->tokenParser = null;
        }
        return $this;
    }

    /**
     * Check if token filter delimiter was set.
     *
     * @return boolean
     */
    public function hasTokenFilterDelimiter()
    {
        return isset($this->tokenFilterDelimiter);
    }

    /**
     * Get token filter delimiter.
     *
     * @return string|null
     */
    public function getTokenFilterDelimiter()
    {
        if (!$this->hasTokenFilterDelimiter()) {
            return null;
        }
        return $this->tokenFilterDelimiter;
    }

    /**
     * Set token filter delimiter.
     *
     * @param string|null $tokenFilterDelimiter The new value for token filter delimiter or null for default.
     * @return $this
     */
    public function setTokenFilterDelimiter($tokenFilterDelimiter = null)
    {
        $this->tokenFilterDelimiter = $tokenFilterDelimiter;
        if ($this->tokenFilterDelimiter !== $tokenFilterDelimiter) {
            $this->tokenParser = null;
        }
        return $this;
    }

    public function getTokenParser()
    {
        if (!isset($this->tokenParser)) {
            $this->tokenParser = new TokenParser();
            if ($this->hasTokenFilterDelimiter()) {
                $this->tokenParser->setFilterDelimiter($this->getTokenFilterDelimiter());
            }
            if ($this->hasTokenRegex()) {
                $this->tokenParser->setTokenRegex($this->getTokenRegex());
            } else {
                if ($this->hasTokenPrefix() && $this->hasTokenSuffix()) {
                    $this->tokenParser->setTokenRegexByDelimiters($this->getTokenPrefix(), $this->getTokenSuffix());
                }
            }
        }
        return $this->tokenParser;
    }

    public function resolve(TokenCollection $tokens)
    {
        $tokens->resolve($this->tokenResolver, null, $this->ignoreUnknownFilters);
        return $this;
    }
}