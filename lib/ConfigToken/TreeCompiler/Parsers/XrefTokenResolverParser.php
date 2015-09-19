<?php

namespace ConfigToken\TreeCompiler\Parsers;


use ConfigToken\TokenResolver\TokenResolverFactory;
use ConfigToken\TokenResolver\Types\RegisteredTokenResolver;
use ConfigToken\TokenResolver\Types\ScopeTokenResolver;
use ConfigToken\TreeCompiler\Exception\TokenResolverDefinitionException;

class XrefTokenResolverParser
{
    const TYPE_KEY = 'type';
    const OPTIONS_KEY = 'options';
    const VALUES_KEY = 'values';
    const VALUES_XREF_KEY = 'values-xref';
    const IGNORE_UNKNOWN_TOKENS_KEY = 'ignore-unknown-tokens';
    const IGNORE_UNKNOWN_FILTERS_KEY = 'ignore-unknown-filters';
    const TOKEN_REGEX_KEY = 'token-regex';
    const TOKEN_PREFIX_KEY = 'token-prefix';
    const TOKEN_SUFFIX_KEY = 'token-suffix';
    const TOKEN_FILTER_DELIMITER_KEY = 'token-filter-delimiter';
    const SCOPE_TOKEN_NAME_KEY = 'scope-token-name';
    const SCOPE_TOKEN_NAME_DELIMITER_KEY = 'scope-token-name-delimiter';
    const SCOPE_TOKEN_LEVEL_DELIMITER_KEY = 'scope-token-level-delimiter';
    const IGNORE_OUT_OF_SCOPE_KEY = 'ignore-out-of-scope';

    public static $DEFAULT_OPTIONS = array(
        self::TYPE_KEY => 'type',
        self::OPTIONS_KEY => array(),
        self::VALUES_KEY => array(),
        self::VALUES_XREF_KEY => null,
        self::IGNORE_UNKNOWN_TOKENS_KEY => true,
        self::IGNORE_UNKNOWN_FILTERS_KEY => true,
        self::TOKEN_REGEX_KEY => null,
        self::TOKEN_PREFIX_KEY => '[[',
        self::TOKEN_SUFFIX_KEY => ']]',
        self::TOKEN_FILTER_DELIMITER_KEY => '|',
        self::SCOPE_TOKEN_NAME_KEY => null,
        self::SCOPE_TOKEN_NAME_DELIMITER_KEY => ':',
        self::SCOPE_TOKEN_LEVEL_DELIMITER_KEY => '.',
        self::IGNORE_OUT_OF_SCOPE_KEY => true,
    );

    const BOOLEAN_TYPE = 'boolean';
    const STRING_TYPE = 'string';


    public static $RESOLVER_OPTION_TYPES = array(
        RegisteredTokenResolver::TYPE => array(
            self::IGNORE_UNKNOWN_TOKENS_KEY => self::BOOLEAN_TYPE,
            self::IGNORE_UNKNOWN_FILTERS_KEY => self::BOOLEAN_TYPE,
            self::TOKEN_REGEX_KEY => self::STRING_TYPE,
            self::TOKEN_PREFIX_KEY => self::STRING_TYPE,
            self::TOKEN_SUFFIX_KEY => self::STRING_TYPE,
            self::TOKEN_FILTER_DELIMITER_KEY => self::STRING_TYPE,
        ),
        ScopeTokenResolver::TYPE => array(
            self::IGNORE_UNKNOWN_TOKENS_KEY => self::BOOLEAN_TYPE,
            self::IGNORE_UNKNOWN_FILTERS_KEY => self::BOOLEAN_TYPE,
            self::IGNORE_OUT_OF_SCOPE_KEY => self::BOOLEAN_TYPE,
            self::TOKEN_REGEX_KEY => self::STRING_TYPE,
            self::TOKEN_PREFIX_KEY => self::STRING_TYPE,
            self::TOKEN_SUFFIX_KEY => self::STRING_TYPE,
            self::TOKEN_FILTER_DELIMITER_KEY => self::STRING_TYPE,
            self::SCOPE_TOKEN_NAME_KEY => self::STRING_TYPE,
            self::SCOPE_TOKEN_NAME_DELIMITER_KEY => self::STRING_TYPE,
            self::SCOPE_TOKEN_LEVEL_DELIMITER_KEY => self::STRING_TYPE,
        ),
    );

    public static $RESOLVER_REQUIRED_OPTION_KEYS = array(
        RegisteredTokenResolver::TYPE => array(
        ),
        ScopeTokenResolver::TYPE => array(
            self::SCOPE_TOKEN_NAME_KEY => true,
        ),
    );

    protected static function validateOptions($tokenResolverBaseType, $options)
    {
        if (!is_array($options)) {
            throw new TokenResolverDefinitionException(
                sprintf(
                    'The "%s" key for the token resolver definition must be an associative array.',
                    static::OPTIONS_KEY
                )
            );
        }
        $required = static::$RESOLVER_REQUIRED_OPTION_KEYS[$tokenResolverBaseType];
        $unknown = array();
        $found = array();
        foreach ($options as $optionKey => $optionValue) {
            if (!isset(static::$RESOLVER_OPTION_TYPES[$tokenResolverBaseType][$optionKey])) {
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
                    'Missing required option(s) "%s" for token resolver definition based on the "%s" type.',
                    implode('", "', $required),
                    $tokenResolverBaseType,
                    static::OPTIONS_KEY
                )
            );
        }
        if (count($unknown) > 0) {
            throw new TokenResolverDefinitionException(
                sprintf(
                    'Unknown option(s) "%s" for token resolver definition based on the "%s" type.',
                    implode('", "', $unknown),
                    $tokenResolverBaseType,
                    static::OPTIONS_KEY
                )
            );
        }
        foreach ($found as $optionKey => $optionValue) {
            $valueType = gettype($optionValue);
            $expectedValueType = static::$RESOLVER_OPTION_TYPES[$tokenResolverBaseType][$optionKey];
            if ($valueType != $expectedValueType) {
                throw new TokenResolverDefinitionException(
                    sprintf(
                        'Wrong type "%s" instead of "%s" for option "%s" for token resolver definition based on the ' .
                        '"%s" type.',
                        $valueType,
                        $expectedValueType,
                        $tokenResolverBaseType,
                        static::OPTIONS_KEY
                    )
                );
            }
        }
    }

    public static function validateDefinition($definition)
    {
        if (!is_array($definition)) {
            throw new TokenResolverDefinitionException(
                sprintf(
                    'Token resolver definition must be an associative array.\n%s',
                    json_encode($definition)
                )
            );
        }
        if (!isset($definition[static::TYPE_KEY])) {
            throw new TokenResolverDefinitionException(
                sprintf(
                    "Token resolver definition is missing the \"%s\" type identifier key.\n%s",
                    static::TYPE_KEY,
                    json_encode($definition)
                )
            );
        }
        $tokenResolverType = $definition[static::TYPE_KEY];
        if (!TokenResolverFactory::isRegisteredByType($tokenResolverType)) {
            throw new TokenResolverDefinitionException(
                sprintf(
                    'Unknown token resolver type identifier "%s".',
                    $tokenResolverType
                )
            );
        }
        $hasValues = isset($definition[static::VALUES_KEY]);
        $hasValuesXref = isset($definition[static::VALUES_XREF_KEY]);
        if ((!$hasValues) && (!$hasValuesXref)) {
            throw new TokenResolverDefinitionException(
                sprintf(
                    'Token resolver definition does not have a "%s" key or a "%s" key.',
                    static::VALUES_KEY,
                    static::VALUES_XREF_KEY
                )
            );
        }
        $tokenResolverBaseType = TokenResolverFactory::getBaseTypeForType($tokenResolverType);
        if (isset($definition[self::OPTIONS_KEY])) {
            $options = $definition[self::OPTIONS_KEY];
            self::validateOptions(
                $tokenResolverBaseType,
                $options
            );
        }
        if ($hasValues) {
            $values = $definition[$this->xrefTokenResolverValuesKey];
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
        } else { // has values xref
            $xrefInfo = $definition[$this->xrefTokenResolverValuesXrefKey];
            $xref = $this->parseXrefInfo(
                sprintf('%s.%s[%d]', $xrefKey, $this->includeXrefResolversKey, $tokenResolverDefinitionIndex),
                $xrefInfo,
                $tokenResolvers,
                $xrefPath
            );
            $resolverValues[$tokenResolverKey] = $xref;
        }
        $tokenResolverDefinitionIndex++;
    }

    public static function parse($definition, $validate = true)
    {
        if ($validate) {
            self::validateDefinition($definition);
        }
        if (isset($definition[static::OPTIONS_KEY])) {
            $options = $definition[static::OPTIONS_KEY];
        } else {
            $options = null;
        }
        $tokenResolver = TokenResolverFactory::get($definition[static::TYPE_KEY]);
        $xrefTokenResolver = new XrefTokenResolver($tokenResolver);
        $values = $resolverValues[$tokenResolverKey];
        if ($values instanceof Xref) {
            $xrefTokenResolver->setXref($values);
        } else {
            $xrefTokenResolver->setRegisteredTokenValues($values);
        }
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
   }
}