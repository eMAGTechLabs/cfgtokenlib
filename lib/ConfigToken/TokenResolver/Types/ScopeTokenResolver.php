<?php

namespace ConfigToken\TokenResolver\Types;

use ConfigToken\TokenResolver\Exception\OutOfScopeException;
use ConfigToken\TokenResolver\Exception\ScopeTokenValueSerializationException;
use ConfigToken\TokenResolver\Exception\TokenFormatException;
use ConfigToken\TokenResolver\Exception\UnknownTokenException;
use ConfigToken\TokenResolver\ScopeTokenValueSerializerInterface;


/**
 * Class ScopeTokenResolver
 *
 * Resolve tokens based on a scope represented by an associative array with string keys.
 * The token format is: $scopeName$scopeNameDelimiter$scopeLevel1[$scopeLevelDelimiter$scopeLevelN]]
 *
 * Examples:
 *   json:firstLevel.secondLevel
 *   (the scope path is: firstLevel.secondLevel)
 *   - scopeTokenName = 'json'
 *   - scopeTokenNameDelimiter = ':'
 *   - scopeLevelDelimiter = '.'
 *
 *  will result in the value: 'exampleValue'
 *  if the scope array is:
 *  scope = array(
 *      'firstLevel' => array(
 *          'secondLevel' => 'exampleValue'
 *      )
 *  )
 *
 *  or will result in the value: "{'exampleValue': 5}"
 *  if the scope array is:
 *  scope = array(
 *      'firstLevel' => array(
 *          'secondLevel' => array(
 *              'exampleValue' => 5
 *          )
 *      )
 *  )
 *  and the serializer is JsonScopeTokenValueSerializer
 *
 * @package ConfigToken\Library\TokenResolver
 */
class ScopeTokenResolver extends AbstractTokenResolver
{
    /** @var string */
    protected $scopeTokenName;

    /** @var array */
    protected $scope = array();

    /** @var string */
    protected $scopeTokenNameDelimiter = ':';

    /** @var string */
    protected $scopeLevelDelimiter = '.';

    /** @var boolean */
    protected $ignoreOutOfScope;

    /** @var ScopeTokenValueSerializerInterface */
    protected $serializer;


    public function __construct($scopeTokenName = null, $scope = null, $ignoreOutOfScope = False)
    {
        $this->setScopeTokenName($scopeTokenName);
        $this->setIgnoreOutOfScope($ignoreOutOfScope);

        if (isset($scope)) {
            $this->setScope($scope);
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
        return 'scope';
    }

    /**
     * Check if the scope token name is set.
     *
     * @return boolean
     */
    public function hasScopeTokenName()
    {
        return isset($this->scopeTokenName);
    }

    /**
     * Set the scope token name.
     *
     * @param string $value
     * @return $this
     */
    public function setScopeTokenName($value)
    {
        $this->scopeTokenName = $value;
        return $this;
    }

    /**
     * Get scope token name.
     *
     * @return string|null
     */
    public function getScopeTokenName()
    {
        return $this->scopeTokenName;
    }

    /**
     * Check if scope was set.
     *
     * @return boolean
     */
    public function hasScope()
    {
        return isset($this->scope);
    }

    /**
     * Get scope.
     *
     * @return array|null
     */
    public function getScope()
    {
        if (!$this->hasScope()) {
            return null;
        }
        return $this->scope;
    }

    /**
     * Set scope.
     *
     * @param array $value The new value.
     * @return $this
     */
    public function setScope($value)
    {
        $this->scope = $value;
        return $this;
    }

    /**
     * Get scope token name delimiter.
     *
     * @return string
     */
    public function getScopeTokenNameDelimiter()
    {
        return $this->scopeTokenNameDelimiter;
    }

    /**
     * Set scope token name delimiter.
     *
     * @param string $value The new value.
     * @return $this
     */
    public function setScopeTokenNameDelimiter($value)
    {
        $this->scopeTokenNameDelimiter = $value;
        return $this;
    }

    /**
     * Get scope level delimiter.
     *
     * @return string
     */
    public function getScopeLevelDelimiter()
    {
        return $this->scopeLevelDelimiter;
    }

    /**
     * Set scope level delimiter.
     *
     * @param string $value The new value.
     * @return $this
     */
    public function setScopeLevelDelimiter($value)
    {
        $this->scopeLevelDelimiter = $value;
        return $this;
    }

    /**
     * Get ignore out of scope flag.
     *
     * @return boolean
     */
    public function getIgnoreOutOfScope()
    {
        return $this->ignoreOutOfScope;
    }

    /**
     * Set ignore out of scope flag.
     *
     * @param boolean $value The new value.
     * @return $this
     */
    public function setIgnoreOutOfScope($value)
    {
        $this->ignoreOutOfScope = $value;
        return $this;
    }

    /**
     * Check if scope token value serializer was set.
     *
     * @return boolean
     */
    public function hasSerializer()
    {
        return isset($this->serializer);
    }

    /**
     * Get scope token value serializer.
     *
     * @return ScopeTokenValueSerializerInterface|null
     */
    public function getSerializer()
    {
        if (!$this->hasSerializer()) {
            return null;
        }
        return $this->serializer;
    }

    /**
     * Set scope token value serializer.
     *
     * @param ScopeTokenValueSerializerInterface $value The new value.
     * @return $this
     */
    public function setSerializer($value)
    {
        $this->serializer = $value;
        return $this;
    }

    /**
     * Get the value at the given tree path from the scope.
     *
     * @param string $pathStr
     * @return mixed
     * @throws \Exception
     * @throws OutOfScopeException
     */
    protected function getFromScopeByPath($pathStr)
    {
        if (!$this->hasScope()) {
            throw new \Exception(sprintf('No scope was set for the %s resolver.', get_called_class()));
        }
        $path = explode($this->scopeLevelDelimiter, $pathStr);
        while (count($path) > 0) {
            $k = count($path)-1;
            if (trim($path[$k]) == '') {
                unset($path[$k]);
            } else {
                break;
            }
        }
        $scopePtr = &$this->scope;
        foreach ($path as $leaf) {
            if (array_key_exists($leaf, $scopePtr)) {
                $scopePtr = &$scopePtr[$leaf];
            } else {
                throw new OutOfScopeException(
                    sprintf(
                        'The path "%s" is outside of the scope set for %s: %s',
                        $pathStr,
                        get_called_class(),
                        json_encode($this->scope)
                    )
                );
            }
        }
        return $scopePtr;
    }

    /**
     * Check if the token value with the given name is registered.
     *
     * @param string $tokenName The identifier of the token value.
     * @return boolean
     */
    public function isTokenValueRegistered($tokenName)
    {
        $t = explode($this->scopeTokenNameDelimiter, $tokenName);
        $token = $t[0];
        if (($token !== $this->scopeTokenName) || (count($t) != 2) || (!$this->hasScope())) {
            return false;
        }
        try {
            $this->getFromScopeByPath($t[1]);
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }

    /**
     * Get the value for the given token.
     *
     * @param string $tokenName The name of the token to be resolved to a value.
     * @param boolean|null $ignoreUnknownTokens If True, passing an unresolvable token will not cause an exception.
     * @param string|null $defaultValue The value returned if the token is not found and set to ignore unknown tokens.
     * @throws OutOfScopeException
     *   If the path portion of the token name does not exist in the given scope and
     *   not set to ignore unknown tokens.
     * @throws ScopeTokenValueSerializationException
     *   If the value at the specified path was not properly serialized to string or no serializer was set.
     * @throws TokenFormatException
     *   If no path was specified.
     * @throws UnknownTokenException
     *   If the name portion of the token name does not match the scope token name and
     *   not set to ignore unknown tokens.
     * @throws \Exception
     * @return null|string
     */
    public function getTokenValue($tokenName, $ignoreUnknownTokens = null, $defaultValue = null)
    {
        if (is_null($ignoreUnknownTokens)) {
            $ignoreUnknownTokens = $this->getIgnoreUnknownTokens();
        }
        $t = explode($this->scopeTokenNameDelimiter, $tokenName);
        $token = $t[0];
        if ($token !== $this->scopeTokenName) {
            if ($ignoreUnknownTokens) {
                return $defaultValue;
            }
            throw new UnknownTokenException($token, $tokenName);
        }
        while (count($t) > 0) {
            $k = count($t) - 1;
            if (trim($t[$k]) == '') {
                unset($t[$k]);
            } else {
                break;
            }
        }
        if (count($t) != 2) {
            throw new TokenFormatException(sprintf('No path specified for scope reference token "%s".', $tokenName));
        }
        try {
            $scopeValue = $this->getFromScopeByPath($t[1]);
        } catch (OutOfScopeException $e) {
            if ($this->ignoreOutOfScope) {
                $scopeValue = $defaultValue;
            } else {
                throw $e;
            }
        }

        if ($this->hasSerializer()) {
            $result = $this->serializer->getSerializedValue($scopeValue);
            if (gettype($result) == 'string') {
                return $result;
            }
            throw new ScopeTokenValueSerializationException(
                sprintf(
                    'Value for scope reference token "%s" of %s was not properly serialized. (%s)',
                    $tokenName,
                    get_called_class(),
                    'Serializer: ' . ($this->hasSerializer() ? get_class($this->serializer) : 'not set')
                )
            );
        } else {
            if (is_string($scopeValue)) {
                return $scopeValue;
            }
            if (is_int($scopeValue)) {
                return sprintf('%d', $scopeValue);
            }
            if (is_float($scopeValue)) {
                return sprintf('%.5f', $scopeValue);
            }
            throw new ScopeTokenValueSerializationException(
                sprintf(
                    'Value of type "%s" for scope reference token "%s" of %s cannot be converted to string. (Serializer not set)',
                    gettype($scopeValue),
                    $tokenName,
                    get_called_class()
                )
            );
        }
    }
}