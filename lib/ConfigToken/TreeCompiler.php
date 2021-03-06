<?php

namespace ConfigToken;

use ConfigToken\Exception\NotRegisteredException;
use ConfigToken\TokenResolver\TokenResolverFactory;
use ConfigToken\TokenResolver\Types\RegisteredTokenResolver;
use ConfigToken\TokenResolver\Types\ScopeTokenResolver;
use ConfigToken\TreeCompiler\Exception\TokenResolverDefinitionException;
use ConfigToken\TreeCompiler\Exception\TreeCompilerFormatException;
use ConfigToken\TreeCompiler\Exception\CircularReferenceException;
use ConfigToken\TreeCompiler\XrefResolver\Exception\UnknownXrefException;
use ConfigToken\TreeCompiler\XrefResolver\Exception\XrefResolverFormatException;
use ConfigToken\TreeCompiler\XrefResolver\Types\InlineXrefResolver;
use ConfigToken\TreeCompiler\XrefResolver\Types\UrlXrefResolver;
use ConfigToken\TreeCompiler\XrefResolver\Types\LocalFileXrefResolver;
use ConfigToken\TreeCompiler\Xref;
use ConfigToken\TreeCompiler\XrefCollection;
use ConfigToken\TreeCompiler\XrefTokenResolver;
use ConfigToken\TreeCompiler\XrefTokenResolverCollection;
use ConfigToken\TreeSerializer\TreeSerializerFactory;


class TreeCompiler
{
    /** @var string */
    protected $includeKey = 'include';
    /** @var string */
    protected $includeXrefKey = 'xref';
    /** @var string */
    protected $includeXrefTypeKey = 'type';
    /** @var string */
    protected $includeXrefLocationKey = 'src';
    /** @var string */
    protected $includeXrefDataKey = 'data';
    /** @var string */
    protected $includeXrefResolversKey = 'resolve';
    /** @var string */
    protected $includeMainKey = 'main';
    /** @var string */
    protected $xrefTokenResolverTypeKey = 'type';
    /** @var string */
    protected $xrefTokenResolverOptionsKey = 'options';
    /** @var string */
    protected $xrefTokenResolverValuesKey = 'values';
    /** @var string */
    protected $xrefTokenResolverValuesXrefKey = 'values-xref';
    /** @var string */
    protected $xrefTokenResolverOptionIgnoreUnknownTokensKey = 'ignore-unknown-tokens';
    /** @var string */
    protected $xrefTokenResolverOptionIgnoreUnknownFiltersKey = 'ignore-unknown-filters';
    /** @var string */
    protected $xrefTokenResolverOptionTokenRegexKey = 'token-regex';
    /** @var string */
    protected $xrefTokenResolverOptionTokenPrefixKey = 'token-prefix';
    /** @var string */
    protected $xrefTokenResolverOptionTokenSuffixKey = 'token-suffix';
    /** @var string */
    protected $xrefTokenResolverOptionTokenFilterDelimiterKey = 'token-filter-delimiter';
    /** @var string */
    protected $xrefTokenResolverOptionScopeTokenNameKey = 'scope-token-name';
    /** @var string */
    protected $xrefTokenResolverOptionScopeTokenNameDelimiterKey = 'scope-token-name-delimiter';
    /** @var string */
    protected $xrefTokenResolverOptionScopeTokenLevelDelimiterKey = 'scope-token-level-delimiter';
    /** @var string */
    protected $xrefTokenResolverOptionIgnoreOutOfScopeKey = 'ignore-out-of-scope';
    /** @var array */
    protected $xrefTokenResolverOptionKeys = array();
    /** @var array */
    protected $xrefTokenResolverRequiredOptionKeys = array();
    /** @var string[] */
    protected $xrefTokenResolverOptionSetterMapping = array();

    /** @var string */
    protected $removeKey = 'remove';
    /** @var string */
    protected $addKey = 'add';
    /** @var string */
    protected $xrefTypeAndLocationDelimiter = ':';

    /** @var XrefCollection */
    protected $xrefs;

    const INCLUDE_TYPE_GROUP = 'group';
    const INCLUDE_TYPE_XREF = 'xref';

    public function __construct(XrefCollection $xrefs = null)
    {
        if (!isset($xrefs)) {
            $xrefs = new XrefCollection();
        }
        $this->xrefs = $xrefs;
        $stringType = gettype('');
        $booleanType = gettype(true);
        $this->xrefTokenResolverOptionKeys = array(
            RegisteredTokenResolver::getBaseType() => array(
                $this->xrefTokenResolverOptionIgnoreUnknownTokensKey => $booleanType,
                $this->xrefTokenResolverOptionIgnoreUnknownFiltersKey => $booleanType,
                $this->xrefTokenResolverOptionTokenRegexKey => $stringType,
                $this->xrefTokenResolverOptionTokenPrefixKey => $stringType,
                $this->xrefTokenResolverOptionTokenSuffixKey => $stringType,
                $this->xrefTokenResolverOptionTokenFilterDelimiterKey => $stringType,
            ),
            ScopeTokenResolver::getBaseType() => array(
                $this->xrefTokenResolverOptionIgnoreUnknownTokensKey => $booleanType,
                $this->xrefTokenResolverOptionIgnoreUnknownFiltersKey => $booleanType,
                $this->xrefTokenResolverOptionIgnoreOutOfScopeKey => $booleanType,
                $this->xrefTokenResolverOptionTokenRegexKey => $stringType,
                $this->xrefTokenResolverOptionTokenPrefixKey => $stringType,
                $this->xrefTokenResolverOptionTokenSuffixKey => $stringType,
                $this->xrefTokenResolverOptionTokenFilterDelimiterKey => $stringType,
                $this->xrefTokenResolverOptionScopeTokenNameKey => $stringType,
                $this->xrefTokenResolverOptionScopeTokenNameDelimiterKey => $stringType,
                $this->xrefTokenResolverOptionScopeTokenLevelDelimiterKey => $stringType,
            )
        );
        $this->xrefTokenResolverRequiredOptionKeys = array(
            RegisteredTokenResolver::getBaseType() => array(),
            ScopeTokenResolver::getBaseType() => array(
                $this->xrefTokenResolverOptionScopeTokenNameKey => true,
            )
        );
    }

    public function getXrefs()
    {
        return $this->xrefs;
    }

    /**
     * Get include key.
     *
     * @return string
     */
    public function getIncludeKey()
    {
        return $this->includeKey;
    }

    /**
     * Set include key.
     *
     * @param string $value The new value.
     * @return $this
     */
    public function setIncludeKey($value)
    {
        $this->includeKey = $value;
        return $this;
    }

    /**
     * Recursively remove the keys of one array from another.
     *
     * @param array $toRemove The array containing the keys to be removed.
     * @param array $removeFrom The array from which to remove keys.
     */
    public function recursiveRemoveData(array &$toRemove, array &$removeFrom)
    {
        foreach ($toRemove as $keyToRemove => $childKeysToRemove) {
            if (is_array($childKeysToRemove)) {
                if (isset($removeFrom[$keyToRemove]) && is_array($removeFrom[$keyToRemove])) {
                    $this->recursiveRemoveData($toRemove[$keyToRemove], $removeFrom[$keyToRemove]);
                }
            } else if (array_key_exists($keyToRemove, $removeFrom)) {
                unset($removeFrom[$keyToRemove]);
            }
        }
    }

    /**
     * Recursively add and override keys from one array into another.
     *
     * @param array $addFrom The source array.
     * @param array $addTo The destination array.
     */
    public function recursiveAddData(array &$addFrom, array &$addTo)
    {
        if (empty($addTo)) {
            /** @noinspection PhpUnusedLocalVariableInspection */
            $addTo = $addFrom;
            return;
        }
        foreach ($addFrom as $keyToAdd => $childKeys) {
            if (is_array($childKeys)) {
                if (isset($addTo[$keyToAdd]) && is_array($addTo[$keyToAdd])) {
                    $this->recursiveAddData($addFrom[$keyToAdd], $addTo[$keyToAdd]);
                    continue;
                }
            }
            $addTo[$keyToAdd] = $addFrom[$keyToAdd];
        }
    }

    /**
     * Retrieve a list of Xref keys to be resolved.
     *
     * @param Xref $xref
     * @param array $xrefDataInclude
     * @param array $xrefDataIncludeXrefs
     * @param string $includeType
     * @param string|array $includeTypeValue
     * @return array
     *
     * @throws XrefResolverFormatException
     */
    protected function getXrefKeysToBeResolved(Xref $xref, $xrefDataInclude, $xrefDataIncludeXrefs,
                                               $includeType, $includeTypeValue)
    {
        $result = array();
        switch ($includeType) {
            case static::INCLUDE_TYPE_XREF:
                $missing = array();
                if (!is_array($includeTypeValue)) {
                    throw new XrefResolverFormatException(
                        $xref,
                        sprintf(
                            'Include type value must be an array, %s given.',
                            gettype($includeTypeValue)
                        )
                    );
                }
                foreach ($includeTypeValue as $xrefKey) {
                    if (!isset($xrefDataIncludeXrefs[$xrefKey])) {
                        $missing[] = $xrefKey;
                    }
                }
                if (empty($missing)) {
                    unset($missing);
                } else {
                    throw new XrefResolverFormatException(
                        $xref,
                        sprintf(
                            "Required to explicitly include the list of Xref keys [\"%s\"] " .
                            "but unable to find [\"%s\"].",
                            implode('", "', $includeTypeValue),
                            implode('", "', $missing)
                        )
                    );
                }
                $result = $includeTypeValue;
                break;
            case static::INCLUDE_TYPE_GROUP:
                if (!isset($xrefDataInclude[$includeTypeValue])) {
                    throw new XrefResolverFormatException(
                        $xref,
                        sprintf(
                            "Unable to find the required include group of Xref keys named \"%s\"",
                            $includeTypeValue
                        )
                    );
                }
                $result = $xrefDataInclude[$includeTypeValue];
                break;
        }
        return $result;
    }

    /**
     * @param $xrefKey
     * @param $tokenResolverDefinitionIndex
     * @param $tokenResolverBaseType
     * @param $options
     * @throws TokenResolverDefinitionException
     */
    protected function validateXrefTokenResolverOptions($xrefKey, $tokenResolverDefinitionIndex, $tokenResolverBaseType,
                                                        $options)
    {
        if (!is_array($options)) {
            throw new TokenResolverDefinitionException(
                sprintf(
                    'The "%s" key for token resolver definition at index %d for Xref key "%s" must be an associative array.',
                    $this->xrefTokenResolverOptionsKey,
                    $tokenResolverDefinitionIndex,
                    $xrefKey
                )
            );
        }
        $required = $this->xrefTokenResolverRequiredOptionKeys[$tokenResolverBaseType];
        $unknown = array();
        $found = array();
        foreach ($options as $optionKey => $optionValue) {
            if (!isset($this->xrefTokenResolverOptionKeys[$tokenResolverBaseType][$optionKey])) {
                $unknown[] = $optionKey;
            } else {
                $found[$optionKey] = $optionValue;
                if (isset($required[$optionKey])) {
                    unset($required[$optionKey]);
                }
            }
        }
        if (count($required) > 0) {
            throw new TokenResolverDefinitionException(
                sprintf(
                    'Missing required option(s) "%s" for token resolver definition based on the "%s" type identifier at ' .
                    'index %d for Xref key "%s".',
                    implode('", "', $required),
                    $tokenResolverBaseType,
                    $this->xrefTokenResolverOptionsKey,
                    $tokenResolverDefinitionIndex,
                    $xrefKey
                )
            );
        }
        if (count($unknown) > 0) {
            throw new TokenResolverDefinitionException(
                sprintf(
                    'Unknown option(s) "%s" for token resolver definition based on the "%s" type identifier at ' .
                    'index %d for Xref key "%s".',
                    implode('", "', $unknown),
                    $tokenResolverBaseType,
                    $this->xrefTokenResolverOptionsKey,
                    $tokenResolverDefinitionIndex,
                    $xrefKey
                )
            );
        }
        foreach ($found as $optionKey => $optionValue) {
            $valueType = gettype($optionValue);
            $expectedValueType = $this->xrefTokenResolverOptionKeys[$tokenResolverBaseType][$optionKey];
            if ($valueType != $expectedValueType) {
                throw new TokenResolverDefinitionException(
                    sprintf(
                        'Wrong type "%s" instead of "%s" for option "%s" for token resolver definition based on the ' .
                        '"%s" type identifier at index %d for Xref key "%s".',
                        $valueType,
                        $expectedValueType,
                        $tokenResolverBaseType,
                        $this->xrefTokenResolverOptionsKey,
                        $tokenResolverDefinitionIndex,
                        $xrefKey
                    )
                );
            }
        }
    }

    /**
     * @param string $xrefKey
     * @param string|array $xrefInfo
     * @param XrefTokenResolverCollection $xrefTokenResolvers
     * @param Xref[] $xrefPath
     * @return Xref|mixed
     * @throws TreeCompilerFormatException
     * @throws \Exception
     */
    protected function parseXrefInfo($xrefKey, $xrefInfo, XrefTokenResolverCollection $xrefTokenResolvers = null, $xrefPath)
    {
        $xrefData = null;
        if (gettype($xrefInfo) == 'string') {
            list($xrefType, $xrefLocation) = Xref::parseDefinitionString($xrefInfo, $this->xrefTypeAndLocationDelimiter);
        } else {
            if (!is_array($xrefInfo)) {
                throw new TreeCompilerFormatException(
                    sprintf(
                        'The Xref definition key "%s" must be a string with the format xref_type %s xref_location ' .
                        'or an associative array with the keys "%s" for type and "%s" for location.',
                        $this->xrefTypeAndLocationDelimiter,
                        $this->includeXrefTypeKey,
                        $this->includeXrefLocationKey
                    )
                );
            }

            $requiredKeyErrorMessage = 'The "%s" key is missing from the Xref definition with the key "%s".';
            if (!isset($xrefInfo[$this->includeXrefTypeKey])) {
                throw new TreeCompilerFormatException(
                    sprintf($requiredKeyErrorMessage, $this->includeXrefTypeKey, $xrefKey)
                );
            }
            $xrefType = $xrefInfo[$this->includeXrefTypeKey];
            if ($xrefType == InlineXrefResolver::getType()) {
                if (!isset($xrefInfo[$this->includeXrefDataKey])) {
                    throw new TreeCompilerFormatException(
                        sprintf($requiredKeyErrorMessage, $this->includeXrefDataKey, $xrefKey)
                    );
                }
                $xrefData = $xrefInfo[$this->includeXrefDataKey];
                $xrefLocation = Xref::computeId($xrefType, serialize($xrefData));
            } else {
                if (!isset($xrefInfo[$this->includeXrefLocationKey])) {
                    throw new TreeCompilerFormatException(
                        sprintf($requiredKeyErrorMessage, $this->includeXrefLocationKey, $xrefKey)
                    );
                }
                $xrefLocation = $xrefInfo[$this->includeXrefLocationKey];
            }
        }

        if (isset($xrefData)) {
            if (isset($xrefTokenResolvers)) {
                $xrefTokenResolvers->applyToArray($xrefData);
            }
        } else {
            if (isset($xrefTokenResolvers)) {
                $xrefLocation = $xrefTokenResolvers->applyToString($xrefLocation);
            }
            $xrefLocation = Xref::computeAbsoluteLocation($xrefType, $xrefLocation, $xrefPath);
        }

        $xrefId = Xref::computeId($xrefType, $xrefLocation);
        if ($this->xrefs->hasById($xrefId)) {
            return $this->xrefs[$xrefId];
        }

        $xref = new Xref($xrefType, $xrefLocation);
        if (isset($xrefData)) {
            $xref->setData($xrefData)->setResolved(true);
        }
        return $xref;
    }

    /**
     * Parse the array corresponding to the $includeKey:$includeXrefKey:<xref name>:$includeXrefResolveKey.
     *
     * @param string $xrefKey
     * @param array $tokenResolversInfo
     * @param XrefTokenResolverCollection $tokenResolvers
     * @param Xref[] $xrefPath
     * @return XrefTokenResolverCollection
     * @throws \Exception
     */
    protected function parseXrefTokenResolverDefinitions($xrefKey, $tokenResolversInfo, XrefTokenResolverCollection $tokenResolvers = null, $xrefPath)
    {
        $resolverValues = array();
        $tokenResolverDefinitionIndex = 0;
        // validate
        if (!is_array($tokenResolversInfo)) {
            throw new TokenResolverDefinitionException(
                sprintf(
                    'Token resolver definitions at index %d for Xref key "%s" must be an array. (%s)',
                    $tokenResolverDefinitionIndex,
                    $xrefKey,
                    json_encode($tokenResolversInfo)
                )
            );
        }
        foreach ($tokenResolversInfo as $tokenResolverKey => $tokenResolverInfo) {
            if (!is_array($tokenResolverInfo)) {
                throw new TokenResolverDefinitionException(
                    sprintf(
                        'Token resolver definition at index %d for Xref key "%s" must be an associative array. (%s)',
                        $tokenResolverKey,
                        $xrefKey,
                        json_encode($tokenResolversInfo)
                    )
                );
            }
            if (!isset($tokenResolverInfo[$this->xrefTokenResolverTypeKey])) {
                throw new TokenResolverDefinitionException(
                    sprintf(
                        "Token resolver definition at index %d for Xref key \"%s\" is missing the \"%s\" type identifier key.\n%s",
                        $tokenResolverDefinitionIndex,
                        $xrefKey,
                        $this->xrefTokenResolverTypeKey,
                        json_encode($tokenResolverInfo)
                    )
                );
            }
            $tokenResolverType = $tokenResolverInfo[$this->xrefTokenResolverTypeKey];
            if (!TokenResolverFactory::isRegisteredByType($tokenResolverType)) {
                throw new TokenResolverDefinitionException(
                    sprintf(
                        'Unknown token resolver type identifier "%s" at index %d for Xref key "%s".',
                        $tokenResolverType,
                        $tokenResolverDefinitionIndex,
                        $xrefKey
                    )
                );
            }
            $hasValues = isset($tokenResolverInfo[$this->xrefTokenResolverValuesKey]);
            $hasValuesXref = isset($tokenResolverInfo[$this->xrefTokenResolverValuesXrefKey]);
            if ((!$hasValues) && (!$hasValuesXref)) {
                throw new TokenResolverDefinitionException(
                    sprintf(
                        'Token resolver definition at index %d for Xref key "%s" does not have a "%s" key or a "%s" key.',
                        $tokenResolverDefinitionIndex,
                        $xrefKey,
                        $this->xrefTokenResolverValuesKey,
                        $this->xrefTokenResolverValuesXrefKey
                    )
                );
            }
            $tokenResolverBaseType = TokenResolverFactory::getBaseTypeForType($tokenResolverType);
            if (isset($tokenResolverInfo[$this->xrefTokenResolverOptionsKey])) {
                $options = $tokenResolverInfo[$this->xrefTokenResolverOptionsKey];
                $this->validateXrefTokenResolverOptions(
                    $xrefKey,
                    $tokenResolverDefinitionIndex,
                    $tokenResolverBaseType,
                    $options
                );
            }
            if ($hasValues) {
                $values = $tokenResolverInfo[$this->xrefTokenResolverValuesKey];
            } else { // has values xref
                $xrefInfo = $tokenResolverInfo[$this->xrefTokenResolverValuesXrefKey];
                $xref = $this->parseXrefInfo(
                    sprintf('%s.%s[%d]', $xrefKey, $this->includeXrefResolversKey, $tokenResolverDefinitionIndex),
                    $xrefInfo,
                    $tokenResolvers,
                    $xrefPath
                );
                // pass down current token resolvers
                $values = $this->recursiveCompileXref($xref, $tokenResolvers, null, null, $xrefPath);
            }
            if (!is_array($values)) {
                throw new TokenResolverDefinitionException(
                    sprintf(
                        'The "%s" key must be an associative array for token resolver definition at ' .
                        'index %d for Xref key "%s".',
                        $this->xrefTokenResolverValuesKey,
                        $tokenResolverDefinitionIndex,
                        $xrefKey
                    )
                );
            }
            $resolverValues[$tokenResolverKey] = $values;
            $tokenResolverDefinitionIndex++;
        }

        $result = new XrefTokenResolverCollection();
        // parse
        foreach ($tokenResolversInfo as $tokenResolverKey => $tokenResolverInfo) {
            if (isset($tokenResolverInfo[$this->xrefTokenResolverOptionsKey])) {
                $options = $tokenResolverInfo[$this->xrefTokenResolverOptionsKey];
            } else {
                $options = null;
            }
            $tokenResolver = TokenResolverFactory::get($tokenResolverInfo[$this->xrefTokenResolverTypeKey]);
            $xrefTokenResolver = new XrefTokenResolver($tokenResolver);
            $values = $resolverValues[$tokenResolverKey];
            $xrefTokenResolver->setRegisteredTokenValues($values);
            if (isset($options)) {
                if (isset($options[$this->xrefTokenResolverOptionIgnoreUnknownTokensKey])) {
                    $tokenResolver->setIgnoreUnknownTokens($options[$this->xrefTokenResolverOptionIgnoreUnknownTokensKey]);
                }
                if ($tokenResolver::getBaseType() == ScopeTokenResolver::getBaseType()) {
                    /** @var ScopeTokenResolver $tokenResolver */
                    if (isset($options[$this->xrefTokenResolverOptionScopeTokenNameKey])) {
                        $tokenResolver->setScopeTokenName($options[$this->xrefTokenResolverOptionScopeTokenNameKey]);
                    }
                    if (isset($options[$this->xrefTokenResolverOptionScopeTokenNameDelimiterKey])) {
                        $tokenResolver->setScopeTokenNameDelimiter($options[$this->xrefTokenResolverOptionScopeTokenNameDelimiterKey]);
                    }
                    if (isset($options[$this->xrefTokenResolverOptionScopeTokenLevelDelimiterKey])) {
                        $tokenResolver->setScopeLevelDelimiter($options[$this->xrefTokenResolverOptionScopeTokenLevelDelimiterKey]);
                    }
                    if (isset($options[$this->xrefTokenResolverOptionIgnoreOutOfScopeKey])) {
                        $tokenResolver->setIgnoreOutOfScope($options[$this->xrefTokenResolverOptionIgnoreOutOfScopeKey]);
                    }
                }
                if (isset($options[$this->xrefTokenResolverOptionIgnoreUnknownFiltersKey])) {
                    $xrefTokenResolver->setIgnoreUnknownFilters($options[$this->xrefTokenResolverOptionIgnoreUnknownFiltersKey]);
                }
                if (isset($options[$this->xrefTokenResolverOptionTokenRegexKey])) {
                    $xrefTokenResolver->setTokenRegex($options[$this->xrefTokenResolverOptionTokenRegexKey]);
                }
                if (isset($options[$this->xrefTokenResolverOptionTokenPrefixKey])) {
                    $xrefTokenResolver->setTokenPrefix($options[$this->xrefTokenResolverOptionTokenPrefixKey]);
                }
                if (isset($options[$this->xrefTokenResolverOptionTokenSuffixKey])) {
                    $xrefTokenResolver->setTokenSuffix($options[$this->xrefTokenResolverOptionTokenSuffixKey]);
                }
                if (isset($options[$this->xrefTokenResolverOptionTokenFilterDelimiterKey])) {
                    $xrefTokenResolver->setTokenFilterDelimiter($options[$this->xrefTokenResolverOptionTokenFilterDelimiterKey]);
                }
            }
            $result->add($xrefTokenResolver);
        }
        return $result;
    }

    /**
     * Recursively resolve Xrefs and compile data.
     *
     * @param Xref $xref
     * @param XrefTokenResolverCollection $tokenResolvers
     * @param string|null $includeType
     * @param string|array|null $includeTypeValue
     * @param array $xrefPath
     * @return array
     *
     * @throws CircularReferenceException
     * @throws Exception\AlreadyRegisteredException
     * @throws TreeCompiler\XrefResolver\Exception\UnknownXrefTypeException
     * @throws UnknownXrefException
     * @throws XrefResolverFormatException
     * @throws \Exception
     */
    protected function recursiveCompileXref(Xref $xref, XrefTokenResolverCollection $tokenResolvers = null,
                                            $includeType = null, $includeTypeValue = null, &$xrefPath)
    {
        static $XREF_KEY = 0;
        static $XREF_RESOLVERS_KEY = 1;

        if (!isset($includeType)) {
            $includeType = static::INCLUDE_TYPE_GROUP;
        }

        switch ($includeType) {
            case static::INCLUDE_TYPE_GROUP:
                if (!isset($includeTypeValue)) {
                    $includeTypeValue = $this->includeMainKey;
                } else if (gettype($includeTypeValue) != 'string') {
                    throw new XrefResolverFormatException(
                        $xref,
                        sprintf(
                            "Include type value must be a string representing the include group name. " .
                            "\"%s\" given instead.",
                            gettype($includeTypeValue)
                        )
                    );
                }
                break;
            case static::INCLUDE_TYPE_XREF:
                if (!is_array($includeTypeValue) || empty($includeTypeValue)) {
                    throw new XrefResolverFormatException(
                        $xref,
                        "Include type value must be a non-empty array of strings for named includes."
                    );
                }
                break;
            default:
                throw new \Exception(sprintf('Unknown include type "%s".', $includeType));
        }
        $mustIncludeSpecificGroup = $includeTypeValue != $this->includeMainKey;

        $xref->resolve();

        $xrefData = $xref->getData();
        if (empty($xrefData)) {
            if (!is_array($xrefData)) {
                throw new XrefResolverFormatException(
                    $xref,
                    "De-serialized data must be an array."
                );
            }
            return array();
        }

        if ((!isset($xrefData[$this->includeKey])) || (!is_array($xrefData[$this->includeKey]))) {
            switch ($includeType) {
                case static::INCLUDE_TYPE_XREF:
                    throw new XrefResolverFormatException(
                        $xref,
                        sprintf(
                            "Required to explicitly include the list of Xref keys [\"%s\"] " .
                            "but the \"%s\" key is missing from the first level.",
                            implode('", "', $includeTypeValue),
                            $this->includeKey
                        )
                    );
                case static::INCLUDE_TYPE_GROUP:
                    if (!$mustIncludeSpecificGroup) {
                        if (isset($xrefData[$this->addKey])) {
                            $result = $xrefData[$this->addKey];
                        } else {
                            if (isset($xrefData[$this->removeKey])) {
                                unset($xrefData[$this->removeKey]);
                            }
                            $result = $xrefData;
                        }
                        if (isset($tokenResolvers)) {
                            $tokenResolvers->applyToArray($result);
                        }
                        return $result;
                    }
                    throw new XrefResolverFormatException(
                        $xref,
                        sprintf(
                            "Required to explicitly include the \"%s\" group of Xref keys " .
                            "but the \"%s\" key is missing from the first level.",
                            $includeTypeValue,
                            $this->includeKey
                        )
                    );
            }
        }
        $xrefDataInclude = &$xrefData[$this->includeKey];

        if ((!isset($xrefDataInclude[$this->includeXrefKey])) || (!is_array($xrefDataInclude[$this->includeXrefKey]))) {
            switch ($includeType) {
                case static::INCLUDE_TYPE_XREF:
                    throw new XrefResolverFormatException(
                        $xref,
                        sprintf(
                            "Required to explicitly include the list of Xref keys [\"%s\"] " .
                            "but the \"%s\" key is missing from the \"%s\" key on the first level.",
                            implode('", "', $includeTypeValue),
                            $this->includeXrefKey,
                            $this->includeKey
                        )
                    );
                case static::INCLUDE_TYPE_GROUP:
                    if (!$mustIncludeSpecificGroup) {
                        if (isset($xrefData[$this->addKey])) {
                            $result = $xrefData[$this->addKey];
                        } else {
                            if (isset($xrefData[$this->removeKey])) {
                                unset($xrefData[$this->removeKey]);
                            }
                            $result = $xrefData;
                        }
                        if (isset($tokenResolvers)) {
                            $tokenResolvers->applyToArray($result);
                        }
                        return $result;
                    }
                    throw new XrefResolverFormatException(
                        $xref,
                        sprintf(
                            "Required to explicitly include the \"%s\" group of Xref keys " .
                            "but the \"%s\" key is missing from the \"%s\" key on the first level.",
                            $includeTypeValue,
                            $this->includeXrefKey,
                            $this->includeKey
                        )
                    );
            }
        }
        $xrefDataIncludeXrefs = &$xrefDataInclude[$this->includeXrefKey];

        $xrefKeysToBeResolved = $this->getXrefKeysToBeResolved(
            $xref,
            $xrefDataInclude,
            $xrefDataIncludeXrefs,
            $includeType,
            $includeTypeValue
        );

        $xrefsToBeParsed = array();
        foreach ($xrefKeysToBeResolved as $xrefKeyToBeResolved) {
            if (!isset($xrefDataIncludeXrefs[$xrefKeyToBeResolved])) {
                throw new UnknownXrefException(
                    sprintf(
                        "Unable to find the required Xref definition named \"%s\".",
                        $xrefKeyToBeResolved
                    )
                );
            }
            $xrefsToBeParsed[$xrefKeyToBeResolved] = $xrefDataIncludeXrefs[$xrefKeyToBeResolved];
        }
        unset($xrefDataIncludeXrefs);

        $xrefId = $xref->getId();
        $xrefPath[$xrefId] = $xref;

        $xrefsToBeResolved = array();
        foreach ($xrefsToBeParsed as $xrefKey => $xrefInfo) {
            $includedXref = $this->parseXrefInfo(
                $xrefKey,
                $xrefInfo,
                $tokenResolvers,
                $xrefPath
            );

            if (is_array($xrefInfo) && isset($xrefInfo[$this->includeXrefResolversKey])) {
                $xrefTokenResolvers = $this->parseXrefTokenResolverDefinitions(
                    $xrefKey,
                    $xrefInfo[$this->includeXrefResolversKey],
                    $tokenResolvers,
                    $xrefPath
                );
            } else {
                $xrefTokenResolvers = null;
            }

            $xrefsToBeResolved[] = array(
                $XREF_KEY => $includedXref,
                $XREF_RESOLVERS_KEY => $xrefTokenResolvers
            );
        }

        /** @var array $includedXrefs */
        $result = array();
        foreach ($xrefsToBeResolved as $xrefToBeResolved) {
            /** @var Xref $includedXref */
            $includedXref = $xrefToBeResolved[$XREF_KEY];
            if (isset($xrefPath[$includedXref->getId()])) {
                throw new CircularReferenceException(
                    sprintf(
                        'Tree compiler encountered circular reference at "%s" in path ["%s"].',
                        sprintf('%s:%s', $includedXref->getType(), $includedXref->getLocation()),
                        implode('", "', $xrefPath)
                    )
                );
            }
            $this->xrefs->add($includedXref);
            /** @var XrefTokenResolverCollection $includeTokenResolvers */
            $includeTokenResolvers = $xrefToBeResolved[$XREF_RESOLVERS_KEY];
            if (isset($includeTokenResolvers)) {
                $downTokenResolvers = $includeTokenResolvers;
            } else {
                $downTokenResolvers = new XrefTokenResolverCollection();
            }
            if (isset($tokenResolvers)) {
                $downTokenResolvers->addCollection($tokenResolvers);
            }
            $includeData = $this->recursiveCompileXref(
                $includedXref,
                $downTokenResolvers,
                static::INCLUDE_TYPE_GROUP,
                $this->includeMainKey,
                $xrefPath
            );
            $this->recursiveAddData($includeData, $result);
        }
        unset($xrefPath[$xrefId]);

        if (isset($xrefData[$this->removeKey])) {
            if (isset($tokenResolvers)) {
                $tokenResolvers->applyToArray($xrefData[$this->removeKey]);
            }
            $this->recursiveRemoveData($xrefData[$this->removeKey], $result);
        }

        if (isset($xrefData[$this->addKey])) {
            if (isset($tokenResolvers)) {
                $tokenResolvers->applyToArray($xrefData[$this->addKey]);
            }
            $this->recursiveAddData($xrefData[$this->addKey], $result);
        }

        return $result;
    }

    public function compileXref(Xref $xref, $includeType = null, $includeTypeValue = null)
    {
        $xrefPath = array();
        $compiledData = $this->recursiveCompileXref($xref, null, $includeType, $includeTypeValue, $xrefPath);
        return $compiledData;
    }

    public function compileLocalFile($inputFileName, $includeType = null, $includeKeyValue = null)
    {
        $xref = new Xref(LocalFileXrefResolver::getType(), $inputFileName);
        return $this->compileXref($xref, $includeType, $includeKeyValue);
    }

    public function compileUrl($inputUrl, $includeType = null, $includeKeyValue = null)
    {
        $xref = new Xref(UrlXrefResolver::getType(), $inputUrl);
        return $this->compileXref($xref, $includeType, $includeKeyValue);
    }

    public function save(array $tree, $fileName)
    {
        $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
        if (!TreeSerializerFactory::isRegisteredByFileExtension($fileExtension)) {
            throw new NotRegisteredException(
                sprintf(
                    'Unable to find serializer for extension "%s".',
                    $fileExtension
                )
            );
        }
        $serializer = TreeSerializerFactory::getByFileExtension($fileExtension);
        $serializedTree = $serializer->serialize($tree);
        file_put_contents($fileName, $serializedTree);
    }
}
